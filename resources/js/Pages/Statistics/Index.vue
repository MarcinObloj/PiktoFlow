<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title, Filler } from 'chart.js';
import { Doughnut, Line } from 'vue-chartjs';

// REJESTRACJA FILLERA - to naprawi błąd!
ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title, Filler);

const props = defineProps({
    statistics: Array
});
const dynamicConclusions = computed(() => {
    const stats = props.statistics;

    if (!stats || stats.length < 2) {
        return {
            title: "Oczekiwanie na dane...",
            text1: "Zgromadź dane z co najmniej dwóch różnych profili, aby system mógł wygenerować wnioski.",
            text2: ""
        };
    }

    const sortedStats = [...stats].sort((a, b) => b.growthRate - a.growthRate);
    const bestGroup = sortedStats[0];
    const worstGroup = sortedStats[sortedStats.length - 1];

    let differenceText = "";
    if (worstGroup.growthRate > 0) {
        let diff = Math.round((bestGroup.growthRate / worstGroup.growthRate) * 100 - 100);
        differenceText = `o ${diff}% lepszy`;
    } else {
        let diff = (bestGroup.growthRate - worstGroup.growthRate).toFixed(1);
        differenceText = `wyższy o ${diff} punktów proc.`;
    }

    return {
        title: "Wnioski z analizy algorytmicznej (Generowane na żywo)",
        text1: `Zestawienie systemowe wykazuje, że najszybszy rozwój wskaźnika MLU występuje u profilu "${bestGroup.child.name}" (tempo wzrostu: +${bestGroup.growthRate}% na dzień). Jest to wynik ${differenceText} w porównaniu do najwolniej rozwijającej się grupy ("${worstGroup.child.name}").`,
        text2: `Predykcja oparta na regresji liniowej potwierdza, że metoda zastosowana w grupie "${bestGroup.child.name}" wykazuje najwyższą skuteczność terapeutyczną. System rekomenduje to podejście jako optymalne do dalszego rozwoju komunikacji.`
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
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-black">{{ stat.child.name }}</h3>
                    <div class="text-sm font-bold bg-gray-100 px-4 py-2 rounded-full">
                        Tempo wzrostu: <span class="text-green-600">+{{ stat.growthRate }}% / dzień</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="h-64">
                        <h4 class="text-center font-bold mb-2">Najczęściej używane</h4>
                        <Doughnut v-if="stat.chartData.length" :data="getDoughnutData(stat.chartLabels, stat.chartData)" />
                        <div v-else class="h-full flex items-center justify-center text-gray-400">Brak danych kliknięć</div>
                    </div>
                    <div class="lg:col-span-2 h-64">
                        <h4 class="text-center font-bold mb-2">Trend MLU i Predykcja</h4>
                        <Line v-if="stat.mluData.length" :data="getLineData(stat.mluLabels, stat.mluData, stat.predictionData)" :options="{responsive:true, maintainAspectRatio:false}" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
