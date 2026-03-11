<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    categories: Array,
});

const speak = (word) => {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(word);
        utterance.lang = 'pl-PL';
        utterance.rate = 0.9;

        window.speechSynthesis.speak(utterance);
    } else {
        console.error('Twoja przeglądarka nie obsługuje Web Speech API.');
    }
};
</script>

<template>
    <Head title="Tablica AAC" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tablica Komunikacyjna</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div v-for="category in categories" :key="category.id" class="mb-8 border-b pb-4">

                        <h3 class="text-2xl font-bold mb-4 uppercase tracking-wider" :style="{ color: category.color }">
                            {{ category.name }}
                        </h3>

                        <div class="flex flex-wrap gap-4">

                            <div v-for="pictogram in category.pictograms" :key="pictogram.id"
                                 @click="speak(pictogram.name)"
                                 class="border-4 rounded-2xl p-4 w-40 h-40 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-100 active:bg-blue-100 active:scale-95 transition-all shadow-md hover:shadow-xl hover:-translate-y-1">

                                <div class="text-6xl mb-2">🖼️</div>
                                <span class="text-xl font-bold text-gray-800 text-center">{{ pictogram.name }}</span>

                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
