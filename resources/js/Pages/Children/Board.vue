<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, nextTick, computed } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';
import confetti from 'canvas-confetti';

const props = defineProps({
    child: Object,
    categories: Array,
});

const isCvi = computed(() => props.child.is_cvi_mode);

const boardClasses = computed(() => ({
    'bg-black text-white': isCvi.value,
    'bg-white text-gray-900': !isCvi.value,
    'min-h-screen flex flex-col relative font-sans transition-colors duration-300': true
}));

const pictogramClasses = computed(() => (
    isCvi.value
        ? 'border-4 border-yellow-400 bg-black rounded-3xl p-4 w-36 h-36 sm:w-40 sm:h-40 flex flex-col items-center justify-center cursor-move transition-all active:scale-95 shadow-[0_0_15px_rgba(250,204,21,0.5)]'
        : 'border-4 border-gray-200 bg-white rounded-3xl p-4 w-36 h-36 sm:w-40 sm:h-40 flex flex-col items-center justify-center cursor-move hover:bg-gray-50 active:bg-blue-100 active:scale-95 transition-all shadow-sm'
));

const localCategories = ref([...props.categories]);
const sentence = ref([]);
const isEyetrackerActive = ref(false);
const isCalibrating = ref(false);

const toggleEyetracker = () => {
    if (!isEyetrackerActive.value) {
        window.webgazer.setGazeListener((data, elapsedTime) => {
            if (data == null) return;
        }).begin();

        window.webgazer.showVideoPreview(true).showPredictionPoints(true);
        isEyetrackerActive.value = true;
        isCalibrating.value = true;
    } else {
        window.webgazer.end();
        isEyetrackerActive.value = false;
        isCalibrating.value = false;
    }
};

const finishCalibration = () => {
    isCalibrating.value = false;
    window.webgazer.showVideoPreview(false);
};

const handleDragEnd = (categoryId) => {
    const category = localCategories.value.find(c => c.id === categoryId);
    const ids = category.pictograms.map(p => p.id);

    axios.post(route('children.reorder', props.child.id), { ids })
        .catch(err => console.error("Błąd zapisu kolejności:", err));
};

const playPictogram = (pictogram) => {
    if (pictogram.audio_path) {
        const audio = new Audio(pictogram.audio_path);
        audio.play();
    } else if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(pictogram.name);
        utterance.lang = 'pl-PL';
        utterance.rate = 0.9;
        window.speechSynthesis.speak(utterance);
    }
};

const addToSentence = (pictogram) => {
    sentence.value.push(pictogram);
    playPictogram(pictogram);
    axios.post(route('children.log-click', props.child.id), {
        pictogram_id: pictogram.id
    }).catch(error => {
        console.error("Nie udało się zapisać statystyki kliknięcia", error);
    });
};

const triggerConfetti = () => {
    const colors = isCvi.value ? ['#fbbf24', '#ffffff', '#eab308'] : ['#3b82f6', '#ef4444', '#10b981', '#f59e0b'];
    confetti({
        particleCount: 150,
        spread: 80,
        origin: { y: 0.6 },
        colors: colors,
        zIndex: 100
    });
};

const speakSentence = () => {
    if (sentence.value.length === 0) return;
    window.speechSynthesis.cancel();

    const playNext = (index) => {
        if (index >= sentence.value.length) {
            triggerConfetti();
            return;
        }
        const p = sentence.value[index];

        if (p.audio_path) {
            const audio = new Audio(p.audio_path);
            audio.onended = () => playNext(index + 1);
            audio.play();
        } else if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(p.name);
            utterance.lang = 'pl-PL';
            utterance.rate = 0.9;
            utterance.onend = () => playNext(index + 1);
            window.speechSynthesis.speak(utterance);
        } else {
            playNext(index + 1);
        }
    };
    playNext(0);
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
    nextTick(() => pinInputRef.value?.focus());
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
    }
};
</script>

<template>
    <Head :title="'Tablica - ' + child.name" />

    <div :class="boardClasses">
        <div class="absolute top-4 right-4 z-40 flex gap-4">

            <button v-if="!isEyetrackerActive" @click="toggleEyetracker"
                    :class="[isCvi ? 'bg-gray-900 text-white border-gray-700' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50']"
                    class="transition-colors flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold border shadow-sm active:scale-95">
                <span class="text-xl">👁️</span> Włącz Eyetracker
            </button>

            <button v-else-if="isCalibrating" @click="finishCalibration"
                    :class="[isCvi ? 'bg-yellow-400 text-black shadow-[0_0_15px_rgba(250,204,21,0.6)]' : 'bg-green-500 text-white border-green-600 hover:bg-green-600']"
                    class="transition-colors flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold border shadow-md animate-pulse active:scale-95">
                <span class="text-xl">🎯</span> Zakończ kalibrację
            </button>

            <button v-else @click="toggleEyetracker"
                    :class="[isCvi ? 'bg-red-900 text-white border-red-700' : 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100']"
                    class="transition-colors flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold border shadow-sm active:scale-95">
                <span class="text-xl">🛑</span> Wyłącz Eyetracker
            </button>

            <button @click="openPinModal"
                    :class="[isCvi ? 'bg-gray-900 text-white border-gray-700' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50']"
                    class="transition-colors flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold border shadow-sm active:scale-95">
                <span class="text-xl">🔒</span> Wyjdź
            </button>
        </div>

        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8 pt-24">
            <div :class="isCvi ? 'bg-black border-4 border-yellow-400 shadow-[0_0_15px_rgba(250,204,21,0.3)]' : 'bg-blue-50 border-4 border-blue-200 shadow-sm'"
                 class="rounded-3xl p-4 mb-8 transition-all">
                <div class="flex items-center gap-4 min-h-[120px]">
                    <div :class="isCvi ? 'bg-black border-yellow-400/50' : 'bg-white border-blue-200'"
                         class="flex-1 flex flex-wrap gap-2 p-4 rounded-2xl border-2 border-dashed min-h-[120px] items-center text-gray-400">
                        <span v-if="sentence.length === 0" class="text-lg font-medium ml-2">Ułóż zdanie klikając w obrazki...</span>

                        <div v-for="(p, index) in sentence" :key="index" @click="sentence.splice(index, 1)"
                             :class="isCvi ? 'border-yellow-400 bg-black' : 'border-gray-200 bg-white'"
                             class="border-2 rounded-xl p-2 w-24 h-24 flex flex-col items-center justify-center shadow-sm hover:bg-red-50 cursor-pointer transition-transform hover:scale-105 active:scale-95">
                            <img v-if="p.image_path" :src="p.image_path" class="w-10 h-10 object-contain mb-1 rounded" />
                            <span :class="isCvi ? 'text-white' : 'text-gray-800'" class="text-xs font-bold text-center truncate w-full">{{ p.name }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button @click="speakSentence"
                                :class="isCvi ? 'bg-yellow-400 text-black hover:bg-yellow-500' : 'bg-blue-500 text-white hover:bg-blue-600'"
                                class="p-4 rounded-2xl shadow-md h-[56px] w-[56px] flex items-center justify-center transition-all active:scale-90 text-2xl">
                            🔊
                        </button>
                        <button @click="clearSentence"
                                :class="isCvi ? 'bg-gray-800 text-white hover:bg-gray-700' : 'bg-red-500 text-white hover:bg-red-600'"
                                class="p-4 rounded-2xl shadow-md h-[56px] w-[56px] flex items-center justify-center transition-all active:scale-90 text-2xl">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>

            <div v-for="category in localCategories" :key="category.id" class="mb-12">
                <h3 :class="isCvi ? 'text-yellow-400 font-black' : 'font-bold text-gray-700'"
                    class="text-2xl mb-6 uppercase tracking-wider px-2"
                    :style="!isCvi ? { color: category.color } : {}">
                    {{ category.name }}
                </h3>

                <draggable
                    v-model="category.pictograms"
                    @end="handleDragEnd(category.id)"
                    item-key="id"
                    class="flex flex-wrap gap-4 sm:gap-6"
                    ghost-class="opacity-30"
                    :animation="250"
                    :delay="200"
                    :delayOnTouchOnly="true"
                >
                    <template #item="{ element }">
                        <div @click="addToSentence(element)" :class="pictogramClasses">
                            <img v-if="element.image_path" :src="element.image_path" class="w-16 h-16 sm:w-20 sm:h-20 object-contain mb-3 rounded" />
                            <div v-else class="text-5xl sm:text-6xl mb-3">🖼️</div>
                            <span :class="isCvi ? 'text-white' : 'text-gray-800'" class="text-lg sm:text-xl font-bold text-center leading-tight">{{ element.name }}</span>
                        </div>
                    </template>
                </draggable>
            </div>
        </div>

        <div v-if="showPinModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div :class="[isCvi ? 'bg-black border-yellow-400' : 'bg-white border-gray-100', 'rounded-3xl p-8 max-w-sm w-full text-center border-4 shadow-2xl flex flex-col']">
                <div class="text-6xl mb-4">🔒</div>
                <h3 :class="[isCvi ? 'text-yellow-400' : 'text-gray-900', 'text-2xl font-black mb-6']">Blokada Rodzicielska</h3>
                <input ref="pinInputRef" v-model="pin" type="password" maxlength="4" :class="[isCvi ? 'bg-black border-yellow-400 text-yellow-400' : 'bg-gray-50 border-gray-300 text-gray-900', 'w-full text-center text-4xl tracking-[0.5em] py-4 rounded-xl border-2 outline-none mb-6']" @keyup.enter="verifyPin" />
                <p v-if="pinError" class="text-red-500 text-sm mb-4">{{ pinError }}</p>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <button @click="closePinModal" :class="[isCvi ? 'bg-gray-900 text-yellow-500 border-2 border-yellow-500 hover:bg-gray-800' : 'bg-gray-200 text-gray-800 hover:bg-gray-300', 'py-4 font-bold rounded-xl transition-colors']">Anuluj</button>
                    <button @click="verifyPin" :class="[isCvi ? 'bg-yellow-400 text-black hover:bg-yellow-500' : 'bg-blue-600 text-white hover:bg-blue-700', 'py-4 font-bold rounded-xl transition-colors']">Odblokuj</button>
                </div>
            </div>
        </div>
    </div>
</template>
