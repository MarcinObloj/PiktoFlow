<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';

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

const showPinModal = ref(false);
const pin = ref('');
const pinError = ref('');
const pinInputRef = ref(null);

const openPinModal = () => {
    showPinModal.value = true;
    pin.value = '';
    pinError.value = '';
    nextTick(() => {
        if (pinInputRef.value) pinInputRef.value.focus();
    });
};

const closePinModal = () => {
    showPinModal.value = false;
    pin.value = '';
    pinError.value = '';
};

const verifyPin = () => {
    if (pin.value === '1234') {
        router.get(route('children.index'));
    } else {
        pinError.value = 'Nieprawidłowy kod PIN.';
        pin.value = '';
        if (pinInputRef.value) pinInputRef.value.focus();
    }
};
</script>

<template>
    <Head :title="'Tablica - ' + child.name" />

    <div class="min-h-screen bg-white flex flex-col relative font-sans">

        <div class="absolute top-4 right-4 z-40">
            <button @click="openPinModal" class="text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-1 bg-gray-50 px-3 py-2 rounded-full text-sm font-bold border border-gray-100 shadow-sm hover:bg-gray-200">
                <span>🔒</span> Wyjdź
            </button>
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

        <div v-if="showPinModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity">
            <div class="bg-white rounded-[2rem] p-8 sm:p-10 max-w-sm w-full shadow-2xl text-center transform transition-all border-4 border-gray-100">
                <div class="text-6xl mb-4">🔒</div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">Blokada Rodzicielska</h3>
                <p class="text-gray-500 mb-6 font-medium">Podaj kod PIN, aby wyjść z tablicy.</p>

                <input
                    ref="pinInputRef"
                    v-model="pin"
                    type="password"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="4"
                    placeholder="••••"
                    class="w-full text-center text-4xl tracking-[1em] pl-10 pr-4 py-4 rounded-2xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0 outline-none mb-2 bg-gray-50 font-mono font-bold text-gray-800 placeholder:tracking-normal placeholder:text-gray-300"
                    @keyup.enter="verifyPin"
                />

                <p class="text-red-500 text-sm h-6 font-bold flex items-center justify-center">{{ pinError }}</p>

                <div class="flex gap-3 mt-6">
                    <button @click="closePinModal" class="flex-1 px-4 py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-200 transition active:scale-95">Anuluj</button>
                    <button @click="verifyPin" class="flex-1 px-4 py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 active:scale-95">Odblokuj</button>
                </div>

                <p class="text-xs text-gray-400 mt-6 font-medium uppercase tracking-widest">Wpisz: 1234</p>
            </div>
        </div>

    </div>
</template>
