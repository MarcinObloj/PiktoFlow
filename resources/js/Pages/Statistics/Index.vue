<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title } from 'chart.js';
import { Doughnut, Line } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title);

defineProps({
    statistics: Array
});

const getDoughnutData = (labels, data) => ({
    labels: labels,
    datasets: [{
        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        data: data
    }]
});

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' }
    }
};

const getLineData = (labels, data) => ({
    labels: labels,
    datasets: [{
        label: 'Średnia Długość Wypowiedzi (MLU)',
        backgroundColor: '#8b5cf6',
        borderColor: '#8b5cf6',
        data: data,
        tension: 0.3,
        fill: true,
        backgroundColor: 'rgba(139, 92, 246, 0.2)',
    }]
});

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' },
        title: {
            display: true,
            text: 'Rozwój wypowiedzi (ostatnie 14 dni)'
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            suggestedMax: 3
        }
    }
};
</script>

<template>
    <Head title="Statystyki AAC" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                📊 Statystyki Komunikacji i Terapii
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

                <div v-if="statistics.length === 0" class="text-center py-12 bg-white rounded-3xl shadow-sm border border-gray-100">
                    <p class="text-gray-500 text-xl">Nie masz jeszcze danych statystycznych. Dziecko musi ułożyć zdanie na tablicy.</p>
                </div>

                <div v-for="stat in statistics" :key="stat.child.id" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 border-b border-gray-100">
                        <h3 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                            <span class="text-3xl">👦</span> Profil: {{ stat.child.name }}
                        </h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Najczęściej używane słowa -->
                        <div class="flex flex-col">
                            <h4 class="text-lg font-bold text-gray-700 mb-4 text-center">Najczęściej używane słowa</h4>
                            <div v-if="stat.chartData.length > 0" class="h-64 relative">
                                <Doughnut :data="getDoughnutData(stat.chartLabels, stat.chartData)" :options="doughnutOptions" />
                            </div>
                            <div v-else class="flex-1 flex items-center justify-center p-8 text-gray-400 italic bg-gray-50 rounded-2xl border-2 border-dashed">
                                Brak danych o kliknięciach na tablicy.
                            </div>
                        </div>

                        <!-- Rozwój MLU (Mean Length of Utterance) -->
                        <div class="flex flex-col">
                            <h4 class="text-lg font-bold text-gray-700 mb-4 text-center" title="Średnia Długość Wypowiedzi">Postęp zdaniowy (Wskaźnik MLU)</h4>
                            <div v-if="stat.mluData.length > 0" class="h-64 relative">
                                <Line :data="getLineData(stat.mluLabels, stat.mluData)" :options="lineOptions" />
                            </div>
                            <div v-else class="flex-1 flex items-center justify-center p-8 text-gray-400 italic bg-gray-50 rounded-2xl border-2 border-dashed">
                                Brak zapisanych pełnych zdań do analizy MLU. Użyj przycisku głośnika na tablicy.
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
