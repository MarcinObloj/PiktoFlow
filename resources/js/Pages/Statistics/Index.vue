<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title, Filler } from 'chart.js';
import { Doughnut, Line } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title, Filler);

const props = defineProps({
    statistics: Array
});

const dynamicConclusions = computed(() => {
    const stats = props.statistics;

    if (!stats || stats.length < 2) {
        return {
            title: "Oczekiwanie na dane analityczne",
            text1: "Zgromadź dane z co najmniej dwóch profili dzieci, aby móc przygotować zestawienie porównawcze trendów komunikacji.",
            text2: ""
        };
    }

    const sortedStats = [...stats].sort((a, b) => b.growthRate - a.growthRate);
    const bestChild = sortedStats[0];
    const worstChild = sortedStats[sortedStats.length - 1];

    // Minimalna liczba dni z danymi, przy której trend jest choć trochę miarodajny.
    const MIN_DATA_POINTS = 5;

    if ((bestChild.dataPoints ?? 0) < MIN_DATA_POINTS) {
        return {
            title: "Za mało danych na wnioski",
            text1: `Najbardziej aktywny profil ("${bestChild.child.name}") ma dane tylko z ${bestChild.dataPoints ?? 0} dni. To zbyt mało, aby mówić o trendzie rozwoju.`,
            text2: `Aby wykresy i orientacyjna prognoza miały wartość, zaleca się regularne korzystanie z tablicy przez co najmniej ${MIN_DATA_POINTS} różnych dni.`
        };
    }

    let differenceText = "";
    if (worstChild.growthRate > 0) {
        let diff = Math.round((bestChild.growthRate / worstChild.growthRate) * 100 - 100);
        differenceText = `wyższą o ${diff}% dynamikę`;
    } else {
        let diff = (bestChild.growthRate - worstChild.growthRate).toFixed(1);
        differenceText = `o ${diff} pkt proc. wyższe tempo`;
    }

    if (!bestChild.isReliable) {
        return {
            title: "Trend orientacyjny — ograniczona wiarygodność",
            text1: `Najwyższe tempo zmian obserwujemy u dziecka "${bestChild.child.name}" (+${bestChild.growthRate}%/dzień), jednak dopasowanie modelu liniowego jest słabe (R² = ${bestChild.rSquared}), więc wynik należy traktować wyłącznie poglądowo.`,
            text2: `Przy tak niskim dopasowaniu prognoza może być zaburzona przez rozrzut danych. Zaleca się zebranie większej liczby obserwacji, zanim zostaną sformułowane wnioski terapeutyczne.`
        };
    }

    return {
        title: "Zestawienie porównawcze trendów MLU (interpretacja orientacyjna)",
        text1: `W analizowanym okresie najszybszy wzrost średniej długości wypowiedzi (MLU) widać u dziecka "${bestChild.child.name}": +${bestChild.growthRate}%/dzień przy dopasowaniu modelu R² = ${bestChild.rSquared} (${bestChild.dataPoints} dni z danymi). To ${differenceText} w porównaniu z profilem "${worstChild.child.name}".`,
        text2: `Uwaga metodologiczna: przedstawiony trend to prosta regresja liniowa na niewielkiej próbie i nie stanowi formalnego testu istotności. Warto zweryfikować, które piktogramy są wybierane najczęściej, i traktować wnioski jako punkt wyjścia do obserwacji, a nie rozstrzygnięcie.`
    };
});

const getDoughnutData = (labels, data) => ({
    labels: labels,
    datasets: [{
        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        data: data
    }]
});

const getLineData = (labels = [], historyData = [], predictionData = []) => {
    const safePrediction = predictionData || [];
    const safeHistory = historyData || [];
    const safeLabels = labels || [];

    const futureLabels = Array.from({length: safePrediction.length}, (_, i) => `+${i+1}d`);
    const allLabels = [...safeLabels, ...futureLabels];

    const paddedHistory = [...safeHistory, ...Array(safePrediction.length).fill(null)];

    const lastHistoryVal = safeHistory.length > 0 ? safeHistory[safeHistory.length - 1] : 0;
    const paddingLength = safeHistory.length > 0 ? safeHistory.length - 1 : 0;

    const paddedPrediction = [...Array(paddingLength).fill(null), lastHistoryVal, ...safePrediction];

    return {
        labels: allLabels,
        datasets: [
            {
                label: 'Historia MLU',
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                data: paddedHistory,
                tension: 0.3,
                fill: true,
            },
            {
                label: 'Prognoza rozwoju',
                borderColor: '#f59e0b',
                borderDash: [5, 5],
                data: paddedPrediction,
                tension: 0.3,
                fill: false,
            }
        ]
    };
};
</script>

<template>
    <Head title="Analiza Badawcza" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <Icon name="chart" :size="26" /> Panel Analizy Porównawczej
            </h2>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 space-y-8">
            <div class="bg-primary-900 text-white p-8 rounded-card shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <Icon name="sparkles" :size="28" class="text-primary-200" />
                    <h3 class="text-xl font-bold">{{ dynamicConclusions.title }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 italic text-primary-100">
                    <p>{{ dynamicConclusions.text1 }}</p>
                    <p>{{ dynamicConclusions.text2 }}</p>
                </div>
            </div>

            <div v-for="stat in statistics" :key="stat.child.id" class="bg-white dark:bg-surface-dark-muted rounded-card shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex flex-col items-end mb-6">
                    <div class="flex items-center gap-4">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">{{ stat.child.name }}</h3>
                        <div class="text-sm font-bold bg-gray-100 dark:bg-gray-700 dark:text-gray-100 px-4 py-2 rounded-full">
                            Wzrost: <span :class="stat.growthRate > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300'">{{ stat.growthRate > 0 ? '+' : '' }}{{ stat.growthRate }}% / dzień</span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                        Dopasowanie modelu (R²): <strong :class="stat.rSquared > 0.5 ? 'text-green-500' : 'text-red-400'">{{ stat.rSquared }}</strong>
                        <span class="text-gray-400"> · {{ stat.dataPoints }} dni z danymi</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="h-64 flex flex-col">
                        <h4 class="text-center font-bold mb-2 text-gray-700 dark:text-gray-200">Najczęściej używane</h4>
                        <div class="w-full h-full flex-1 min-h-[12rem] relative">
                            <Doughnut v-if="stat.chartData?.length > 0" :data="getDoughnutData(stat.chartLabels, stat.chartData)" :options="{responsive:true, maintainAspectRatio:false}" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                Brak danych kliknięć
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-2 h-64 flex flex-col">
                        <h4 class="text-center font-bold mb-2 text-gray-700 dark:text-gray-200">Trend MLU i Predykcja</h4>
                        <div class="w-full h-full flex-1 min-h-[12rem] relative">
                            <Line v-if="stat.mluData?.length > 0" :data="getLineData(stat.mluLabels, stat.mluData, stat.predictionData)" :options="{responsive:true, maintainAspectRatio:false}" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 font-medium bg-gray-50 dark:bg-gray-800 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                Brak historii zbudowanych zdań (MLU) do analizy.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
