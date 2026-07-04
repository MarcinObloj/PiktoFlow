<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
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
            title: "Oczekiwanie na dane analityczne...",
            text1: "Zgromadź dane z co najmniej dwóch różnych profili dzieci, aby algorytm mógł wyznaczyć wariancję i wygenerować wnioski analityczne.",
            text2: ""
        };
    }

    const sortedStats = [...stats].sort((a, b) => b.growthRate - a.growthRate);
    const bestChild = sortedStats[0];
    const worstChild = sortedStats[sortedStats.length - 1];

    let differenceText = "";
    if (worstChild.growthRate > 0) {
        let diff = Math.round((bestChild.growthRate / worstChild.growthRate) * 100 - 100);
        differenceText = `wyższą o ${diff}% dynamikę postępów`;
    } else {
        let diff = (bestChild.growthRate - worstChild.growthRate).toFixed(1);
        differenceText = `o ${diff} punktów proc. wyższe tempo rozwoju`;
    }

    if (!bestChild.isSignificant) {
        return {
            title: "Ostrzeżenie: Dane nieistotne statystycznie",
            text1: `Zestawienie wykazuje najwyższe tempo rozwoju u dziecka "${bestChild.child.name}" (+${bestChild.growthRate}%/dzień), ale model regresji wykazuje niską determinację (R² = ${bestChild.rSquared}).`,
            text2: `Zaleca się zebranie większej ilości danych z logów komunikacji, zanim wyciągnięte zostaną stanowcze wnioski pedagogiczne. Aktualne predykcje mogą być zaburzone przez wariancję (szum danych).`
        };
    }

    return {
        title: "Wnioski z analizy algorytmicznej (Rozwój MLU)",
        text1: `Zestawienie porównawcze wykazuje najwyższą skuteczność w budowaniu komunikacji u dziecka "${bestChild.child.name}". Dzienny przyrost wskaźnika MLU na poziomie +${bestChild.growthRate}% (R² = ${bestChild.rSquared}) oznacza ${differenceText} w zestawieniu z profilem "${worstChild.child.name}". Taka asymetria sugeruje, że obecny układ tablic wyjątkowo dobrze odpowiada na potrzeby tego użytkownika.`,
        text2: `Krzywa regresji dla profilu "${bestChild.child.name}" potwierdza istotność statystyczną trendu wzrostowego. Rekomenduje się przeanalizowanie najczęściej wybieranych przez niego piktogramów i próbę zaimplementowania podobnej strategii modelowania u pozostałych dzieci w celu stymulacji ich rozwoju językowego.`
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
            <h2 class="font-bold text-2xl text-gray-800">📊 Panel Analizy Porównawczej</h2>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 space-y-8">
            <div class="bg-blue-900 text-white p-8 rounded-3xl shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-3xl animate-pulse">🤖</span>
                    <h3 class="text-xl font-bold">{{ dynamicConclusions.title }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 italic text-blue-100">
                    <p>{{ dynamicConclusions.text1 }}</p>
                    <p>{{ dynamicConclusions.text2 }}</p>
                </div>
            </div>

            <div v-for="stat in statistics" :key="stat.child.id" class="bg-white rounded-3xl shadow-lg border p-6">
                <div class="flex flex-col items-end mb-6">
                    <div class="flex items-center gap-4">
                        <h3 class="text-xl font-black">{{ stat.child.name }}</h3>
                        <div class="text-sm font-bold bg-gray-100 px-4 py-2 rounded-full">
                            Wzrost: <span :class="stat.growthRate > 0 ? 'text-green-600' : 'text-gray-600'">{{ stat.growthRate > 0 ? '+' : '' }}{{ stat.growthRate }}% / dzień</span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 font-medium mt-1">
                        Współczynnik R² (istotność): <strong :class="stat.rSquared > 0.5 ? 'text-green-500' : 'text-red-400'">{{ stat.rSquared }}</strong>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="h-64">
                        <h4 class="text-center font-bold mb-2">Najczęściej używane</h4>
                        <Doughnut v-if="stat.chartData.length" :data="getDoughnutData(stat.chartLabels, stat.chartData)" />
                        <div v-else class="h-full flex items-center justify-center text-gray-400">Brak danych kliknięć</div>
                    </div>
                    <div class="lg:col-span-2 h-64 flex flex-col">
                        <h4 class="text-center font-bold mb-2">Trend MLU i Predykcja</h4>
                        <div class="flex-1 relative">
                            <Line v-if="stat.mluData.length" :data="getLineData(stat.mluLabels, stat.mluData, stat.predictionData)" :options="{responsive:true, maintainAspectRatio:false}" />
                            <div v-else class="absolute inset-0 flex items-center justify-center text-gray-400 font-medium bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                Brak historii zbudowanych zdań (MLU) do analizy.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
