<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    child: Object,
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
    <Head :title="'Tablica - ' + child.name" />

    <div class="min-h-screen bg-white flex flex-col relative font-sans">

        <div class="absolute top-4 right-4 z-50">
            <Link :href="route('children.index')" class="text-gray-300 hover:text-gray-600 transition-colors flex items-center gap-1 bg-gray-50 px-3 py-1 rounded-full text-sm font-medium border border-gray-100 shadow-sm">
                <span>🔒</span> Wyjdź
            </Link>
        </div>

        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8 pt-16">

            <div class="bg-blue-50 border-4 border-blue-200 rounded-3xl p-4 mb-8 shadow-sm">
                <div class="flex items-center gap-4 min-h-[120px]">

                    <div class="flex-1 flex flex-wrap gap-2 p-4 bg-white rounded-2xl border-2 border-dashed border-blue-200 min-h-[120px] items-center">
                        <span v-if="sentence.length === 0" class="text-gray-400 font-medium text-lg">Ułóż zdanie klikając w obrazki...</span>

                        <div v-for="(p, index) in sentence" :key="index" class="border-2 border-gray-200 rounded-xl p-2 w-24 h-24 flex flex-col items-center justify-center bg-white shadow-sm hover:bg-red-50 cursor-pointer transition-colors" @click="sentence.splice(index, 1)" title="Usuń z paska">
                            <img v-if="p.image_path" :src="p.image_path" @error="p.image_path = null" :alt="p.name" class="w-10 h-10 object-contain mb-1 rounded" />
                            <div v-else class="text-3xl mb-1">🖼️</div>
                            <span class="text-xs font-bold text-gray-800 text-center leading-tight truncate w-full">{{ p.name }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button @click="speakSentence" class="bg-blue-500 hover:bg-blue-600 text-white font-bold p-4 rounded-2xl shadow-md transition-all flex items-center justify-center h-16 w-16 active:scale-95">
                            <span class="text-2xl">🔊</span>
                        </button>
                        <button @click="clearSentence" class="bg-red-500 hover:bg-red-600 text-white font-bold p-4 rounded-2xl shadow-md transition-all flex items-center justify-center h-16 w-16 active:scale-95">
                            <span class="text-2xl">🗑️</span>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="categories.length === 0" class="text-center py-20 text-gray-500 text-xl font-medium">
                Brak przypisanych piktogramów. <br>Wróć do panelu (🔒) i kliknij "Zarządzaj słowami".
            </div>

            <div v-for="category in categories" :key="category.id" class="mb-10">
                <h3 class="text-xl font-bold mb-4 uppercase tracking-wider px-2" :style="{ color: category.color }">
                    {{ category.name }}
                </h3>

                <div class="flex flex-wrap gap-4">
                    <div v-for="pictogram in category.pictograms" :key="pictogram.id"
                         @click="addToSentence(pictogram)"
                         class="border-4 rounded-3xl p-4 w-36 h-36 sm:w-40 sm:h-40 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 active:bg-blue-100 active:scale-95 transition-all shadow-sm hover:shadow-md bg-white border-gray-200">

                        <img v-if="pictogram.image_path" :src="pictogram.image_path" @error="pictogram.image_path = null" :alt="pictogram.name" class="w-16 h-16 sm:w-20 sm:h-20 object-contain mb-2 rounded" />
                        <div v-else class="text-5xl sm:text-6xl mb-2">🖼️</div>

                        <span class="text-lg sm:text-xl font-bold text-gray-800 text-center leading-tight">{{ pictogram.name }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
