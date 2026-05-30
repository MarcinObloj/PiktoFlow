<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    child: Object,
    pictograms: Array,
});

onMounted(() => {
    setTimeout(() => window.print(), 600);
});
</script>

<template>
    <Head :title="'Tablica ' + child.name" />

    <!-- Nagłówek tylko na ekranie -->
    <div class="print:hidden bg-blue-600 text-white px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🧩</span>
            <span class="font-bold text-lg">PiktoFlow — Eksport PDF</span>
        </div>
        <button
            @click="window.print()"
            class="bg-white text-blue-600 font-bold px-5 py-2 rounded-xl shadow hover:bg-blue-50 transition-all"
        >
            🖨️ Drukuj / Zapisz PDF
        </button>
    </div>

    <!-- Strona do druku -->
    <div class="p-6 max-w-4xl mx-auto">
        <div class="text-center mb-6 border-b border-gray-200 pb-4">
            <h1 class="text-2xl font-black text-gray-900">Tablica komunikacyjna</h1>
            <p class="text-lg text-gray-600 mt-1">{{ child.name }}</p>
            <p class="text-sm text-gray-400 mt-1">Wygenerowano przez PiktoFlow • {{ new Date().toLocaleDateString('pl-PL') }}</p>
        </div>

        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-3">
            <div
                v-for="pic in pictograms"
                :key="pic.id"
                class="flex flex-col items-center border border-gray-200 rounded-xl p-2 gap-1 bg-white"
            >
                <img
                    :src="pic.image_path"
                    :alt="pic.name"
                    class="w-16 h-16 object-contain"
                />
                <span class="text-xs font-semibold text-gray-800 text-center leading-tight">{{ pic.name }}</span>
            </div>
        </div>

        <div class="text-center mt-8 text-xs text-gray-300 print:block hidden">
            PiktoFlow — system komunikacji wspomagającej dla dzieci
        </div>
    </div>

    <style>
        @media print {
            @page { margin: 1.5cm; size: A4; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</template>
