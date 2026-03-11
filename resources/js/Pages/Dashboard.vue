<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    categories: Array,
});

const sentence = ref([]);

const speak = (text) => {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'pl-PL';
        utterance.rate = 0.9;

        window.speechSynthesis.speak(utterance);
    } else {
        console.error('Twoja przeglądarka nie obsługuje Web Speech API.');
    }
};

const addToSentence = (pictogram) => {
    sentence.value.push(pictogram);
    speak(pictogram.name);
};

const speakSentence = () => {
    if (sentence.value.length === 0) return;

    const fullText = sentence.value.map(p => p.name).join(' ');
    speak(fullText);
};

const clearSentence = () => {
    sentence.value = [];
};
</script>

<template>
    <Head title="Tablica AAC" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tablica Komunikacyjna</h2>
                <Link :href="route('pictograms.create')" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow transition">
                    + Dodaj własny piktogram
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8 border-4 border-blue-200">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Budowanie zdania:</h3>
                    <div class="flex items-center gap-4 min-h-[120px]">

                        <div class="flex-1 flex flex-wrap gap-2 p-4 bg-gray-50 rounded-xl min-h-[120px] items-center">
                            <span v-if="sentence.length === 0" class="text-gray-400 italic">Kliknij piktogramy poniżej, aby ułożyć zdanie...</span>

                            <div v-for="(p, index) in sentence" :key="index" class="border-2 border-gray-300 rounded-lg p-2 w-24 h-24 flex flex-col items-center justify-center bg-white shadow-sm hover:bg-red-50 cursor-pointer" @click="sentence.splice(index, 1)" title="Kliknij, aby usunąć ten piktogram">
                                <img v-if="p.image_path" :src="p.image_path" @error="p.image_path = null" :alt="p.name" class="w-10 h-10 object-contain mb-1 rounded" />
                                <div v-else class="text-3xl mb-1">🖼️</div>
                                <span class="text-xs font-bold text-gray-800 text-center leading-tight truncate w-full">{{ p.name }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button @click="speakSentence" class="bg-blue-500 hover:bg-blue-600 text-white font-bold p-4 rounded-xl shadow transition flex items-center justify-center h-16 w-16" title="Czytaj całe zdanie">
                                <span class="text-2xl">🔊</span>
                            </button>
                            <button @click="clearSentence" class="bg-red-500 hover:bg-red-600 text-white font-bold p-4 rounded-xl shadow transition flex items-center justify-center h-16 w-16" title="Wyczyść zdanie">
                                <span class="text-2xl">🗑️</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div v-for="category in categories" :key="category.id" class="mb-8 border-b pb-4">

                        <h3 class="text-2xl font-bold mb-4 uppercase tracking-wider" :style="{ color: category.color }">
                            {{ category.name }}
                        </h3>

                        <div class="flex flex-wrap gap-4">

                            <div v-for="pictogram in category.pictograms" :key="pictogram.id"
                                 @click="addToSentence(pictogram)"
                                 class="border-4 rounded-2xl p-4 w-40 h-40 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-100 active:bg-blue-100 active:scale-95 transition-all shadow-md hover:shadow-xl hover:-translate-y-1">

                                <img v-if="pictogram.image_path" :src="pictogram.image_path" @error="pictogram.image_path = null" :alt="pictogram.name" class="w-20 h-20 object-contain mb-2 rounded" />
                                <div v-else class="text-6xl mb-2">🖼️</div>

                                <span class="text-xl font-bold text-gray-800 text-center">{{ pictogram.name }}</span>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
