<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

defineProps({
    statistics: Array
});

const getChartData = (labels, data) => ({
    labels: labels,
    datasets: [{
        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        data: data
    }]
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' }
    }
};
</script>

<template>
    <Head title="Statystyki AAC" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                📊 Statystyki Komunikacji
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div v-if="statistics.length === 0" class="text-center py-12">
                    <p class="text-gray-500 text-xl">Nie masz jeszcze dodanych profili dzieci.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div v-for="stat in statistics" :key="stat.child.id" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col">
                        <h3 class="text-2xl font-black text-gray-900 mb-2 border-b border-gray-100 pb-4">
                            Profil: {{ stat.child.name }}
                        </h3>

                        <div v-if="stat.chartData.length > 0" class="h-64 relative mt-4">
                            <Doughnut :data="getChartData(stat.chartLabels, stat.chartData)" :options="chartOptions" />
                        </div>

                        <div v-else class="flex-1 flex items-center justify-center p-8 text-gray-400 italic">
                            Brak danych o kliknięciach na tablicy.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
