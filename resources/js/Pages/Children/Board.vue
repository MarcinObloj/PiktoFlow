<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, nextTick, computed, onMounted, watch } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';
import confetti from 'canvas-confetti';
import { useTTS } from '@/Composables/useTTS';

const props = defineProps({
    child: Object,
    categories: Array,
});

const { speak, stop, voices, selectedVoice, rate, pitch, volume } = useTTS();
const sentence = ref([]);
const localCategories = ref([...props.categories]);
const predictedPictograms = ref([]);

const fetchPredictions = async () => {
    const lastId = sentence.value.length > 0 ? sentence.value[sentence.value.length - 1].id : null;
    try {
        const response = await axios.get(route('children.predict', props.child.id), {
            params: { last_pictogram_id: lastId }
        });
        predictedPictograms.value = response.data;
    } catch (error) {
        console.error("Błąd podczas pobierania predykcji", error);
    }
};

watch(sentence, () => {
    fetchPredictions();
}, { deep: true });

onMounted(() => {
    // Initialize TTS settings from child props
    rate.value = props.child.tts_rate || 0.9;
    pitch.value = props.child.tts_pitch || 1.0;
    volume.value = props.child.tts_volume || 1.0;
    
    // Set voice if stored
    if (props.child.tts_voice) {
        const timer = setInterval(() => {
            if (voices.value.length > 0) {
                const voice = voices.value.find(v => v.name === props.child.tts_voice);
                if (voice) selectedVoice.value = voice;
                clearInterval(timer);
            }
        }, 100);
    }
    
    fetchPredictions(); // Initial fetch
});

const isCvi = computed(() => props.child.is_cvi_mode);

const boardClasses = computed(() => ({
    'bg-black text-white': isCvi.value,
    'bg-white text-gray-900': !isCvi.value,
    'min-h-screen flex flex-col relative font-sans transition-colors duration-300': true
}));

const isEyetrackerActive = ref(false);
const isCalibrating = ref(false);

const pictogramClasses = computed(() => {
    const base = 'eyetracker-target relative overflow-hidden flex flex-col items-center justify-center cursor-pointer transition-all active:scale-95 shadow-sm rounded-3xl p-4 ';

    const size = isEyetrackerActive.value && !isCalibrating.value
        ? 'w-48 h-48 sm:w-56 sm:h-56 border-8 '
        : 'w-36 h-36 sm:w-40 sm:h-40 border-4 ';

    const theme = isCvi.value
        ? 'border-yellow-400 bg-black shadow-[0_0_15px_rgba(250,204,21,0.5)] '
        : 'border-gray-200 bg-white hover:bg-gray-50 active:bg-blue-100 ';

    return base + size + theme;
});

const hoveredPictogramId = ref(null);
const dwellProgress = ref(0);
let dwellStartTime = 0;
let lastTriggeredTime = 0;

const gazeHistory = [];
const historyLength = 15;
let leaveTimeout = null;

const toggleEyetracker = () => {
    if (!isEyetrackerActive.value) {
        window.webgazer.applyKalmanFilter(true);
        window.webgazer.setGazeListener((data, elapsedTime) => {
            if (data == null || isCalibrating.value) return;

            gazeHistory.push({ x: data.x, y: data.y });
            if (gazeHistory.length > historyLength) gazeHistory.shift();

            const avgX = gazeHistory.reduce((sum, p) => sum + p.x, 0) / gazeHistory.length;
            const avgY = gazeHistory.reduce((sum, p) => sum + p.y, 0) / gazeHistory.length;

            const viewportX = avgX - window.scrollX;
            const viewportY = avgY - window.scrollY;

            const el = document.elementFromPoint(viewportX, viewportY);
            const target = el ? el.closest('.eyetracker-target') : null;

            if (target) {
                const picId = target.getAttribute('data-id');

                if (leaveTimeout) {
                    clearTimeout(leaveTimeout);
                    leaveTimeout = null;
                }

                if (hoveredPictogramId.value !== picId) {
                    hoveredPictogramId.value = picId;
                    dwellStartTime = elapsedTime;
                    dwellProgress.value = 0;
                } else {
                    if (elapsedTime - lastTriggeredTime < 1500) {
                        dwellStartTime = elapsedTime;
                        dwellProgress.value = 0;
                        return;
                    }

                    const progress = ((elapsedTime - dwellStartTime) / 2000) * 100;
                    dwellProgress.value = Math.min(progress, 100);

                    if (progress >= 100) {
                        const found = localCategories.value.flatMap(c => c.pictograms).find(p => p.id == picId);
                        if (found) addToSentence(found);

                        lastTriggeredTime = elapsedTime;
                        dwellProgress.value = 0;
                        hoveredPictogramId.value = null;
                        gazeHistory.length = 0;
                    }
                }
            } else {
                if (!leaveTimeout && hoveredPictogramId.value) {
                    leaveTimeout = setTimeout(() => {
                        hoveredPictogramId.value = null;
                        dwellProgress.value = 0;
                        leaveTimeout = null;
                    }, 600);
                }
            }
        }).begin();

        window.webgazer.showVideoPreview(true).showPredictionPoints(true);
        isEyetrackerActive.value = true;
        isCalibrating.value = true;
    } else {
        window.webgazer.end();
        isEyetrackerActive.value = false;
        isCalibrating.value = false;
        hoveredPictogramId.value = null;
        dwellProgress.value = 0;
        gazeHistory.length = 0;
        if (leaveTimeout) clearTimeout(leaveTimeout);
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
        .catch(err => console.error(err));
};

const playPictogram = (pictogram) => {
    if (pictogram.audio_path) {
        const audio = new Audio(pictogram.audio_path);
        audio.play();
    } else {
        speak(pictogram.name);
    }
};

const addToSentence = (pictogram) => {
    sentence.value.push(pictogram);
    playPictogram(pictogram);
    axios.post(route('children.log-click', props.child.id), {
        pictogram_id: pictogram.id
    }).catch(error => {
        console.error(error);
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
    stop();
    
    // Logowanie ułożonego zdania w celu śledzenia wskaźnika MLU (Mean Length of Utterance)
    axios.post(route('children.log-sentence', props.child.id), {
        pictogram_ids: sentence.value.map(p => p.id)
    }).catch(error => {
        console.error("Błąd podczas logowania zdania", error);
    });

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
        } else {
            speak(p.name, {
                onEnd: () => playNext(index + 1)
            });
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
        if (isEyetrackerActive.value) {
            window.webgazer.end();
        }
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
                <span class="text-xl pointer-events-none">👁️</span> Włącz Eyetracker
            </button>

            <button v-else-if="isCalibrating" @click="finishCalibration"
                    :class="[isCvi ? 'bg-yellow-400 text-black shadow-[0_0_15px_rgba(250,204,21,0.6)]' : 'bg-green-500 text-white border-green-600 hover:bg-green-600']"
                    class="transition-colors flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold border shadow-md animate-pulse active:scale-95">
                <span class="text-xl pointer-events-none">🎯</span> Zakończ kalibrację
            </button>

            <button v-else @click="toggleEyetracker"
                    :class="[isCvi ? 'bg-red-900 text-white border-red-700' : 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100']"
                    class="transition-colors flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold border shadow-sm active:scale-95">
                <span class="text-xl pointer-events-none">🛑</span> Wyłącz Eyetracker
            </button>

            <button @click="openPinModal"
                    :class="[isCvi ? 'bg-gray-900 text-white border-gray-700' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50']"
                    class="transition-colors flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold border shadow-sm active:scale-95">
                <span class="text-xl pointer-events-none">🔒</span> Wyjdź
            </button>
        </div>

        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8 pt-24">
            <div :class="isCvi ? 'bg-black border-4 border-yellow-400 shadow-[0_0_15px_rgba(250,204,21,0.3)]' : 'bg-blue-50 border-4 border-blue-200 shadow-sm'"
                 class="rounded-3xl p-4 mb-8 transition-all flex flex-col gap-4">
                <div class="flex items-center gap-4 min-h-[120px]">
                    <div :class="isCvi ? 'bg-black border-yellow-400/50' : 'bg-white border-blue-200'"
                         class="flex-1 flex flex-wrap justify-center gap-2 p-4 rounded-2xl border-2 border-dashed min-h-[120px] items-center text-gray-400">
                        <span v-if="sentence.length === 0" class="text-lg font-medium text-center">Ułóż zdanie klikając w obrazki...</span>

                        <div v-for="(p, index) in sentence" :key="index" @click="sentence.splice(index, 1)"
                             :class="isCvi ? 'border-yellow-400 bg-black' : 'border-gray-200 bg-white'"
                             class="border-2 rounded-xl p-2 w-24 h-24 flex flex-col items-center justify-center shadow-sm hover:bg-red-50 cursor-pointer transition-transform hover:scale-105 active:scale-95">
                            <img v-if="p.image_path" :src="p.image_path" class="w-10 h-10 object-contain mb-1 rounded pointer-events-none" />
                            <span :class="isCvi ? 'text-white' : 'text-gray-800'" class="text-xs font-bold text-center truncate w-full pointer-events-none">{{ p.name }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button @click="speakSentence"
                                :class="isCvi ? 'bg-yellow-400 text-black hover:bg-yellow-500' : 'bg-blue-500 text-white hover:bg-blue-600'"
                                class="eyetracker-target p-4 rounded-2xl shadow-md h-[56px] w-[56px] flex items-center justify-center transition-all active:scale-90 text-2xl"
                                data-id="speak-btn">
                            🔊
                        </button>
                        <button @click="clearSentence"
                                :class="isCvi ? 'bg-gray-800 text-white hover:bg-gray-700' : 'bg-red-500 text-white hover:bg-red-600'"
                                class="eyetracker-target p-4 rounded-2xl shadow-md h-[56px] w-[56px] flex items-center justify-center transition-all active:scale-90 text-2xl"
                                data-id="clear-btn">
                            🗑️
                        </button>
                    </div>
                </div>
                
                <!-- Predykcje / Sugestie -->
                <div v-if="predictedPictograms.length > 0" class="flex flex-wrap items-center gap-3 justify-center sm:justify-start px-2 py-2 border-t border-gray-200/50 mt-2">
                    <span :class="isCvi ? 'text-yellow-400' : 'text-gray-500'" class="text-sm font-bold flex items-center gap-1">
                        ✨ Podpowiedzi:
                    </span>
                    <button v-for="p in predictedPictograms" :key="'pred-'+p.id" @click="addToSentence(p)"
                            :class="isCvi ? 'bg-black border-yellow-400 text-white hover:bg-yellow-900/30' : 'bg-purple-50 hover:bg-purple-100 border-purple-200 text-purple-900'"
                            class="eyetracker-target flex items-center gap-2 border-2 rounded-xl px-4 py-2 transition-all active:scale-95 shadow-sm"
                            :data-id="p.id">
                        <img v-if="p.image_path" :src="p.image_path" class="w-6 h-6 object-contain pointer-events-none" />
                        <span class="font-bold text-sm pointer-events-none">{{ p.name }}</span>
                    </button>
                </div>
            </div>

            <div v-for="category in localCategories" :key="category.id" class="mb-12 text-center sm:text-left">
                <h3 :class="isCvi ? 'text-yellow-400 font-black' : 'font-bold text-gray-700'"
                    class="text-2xl mb-6 uppercase tracking-wider px-2"
                    :style="!isCvi ? { color: category.color } : {}">
                    {{ category.name }}
                </h3>

                <draggable
                    v-model="category.pictograms"
                    @end="handleDragEnd(category.id)"
                    item-key="id"
                    class="flex flex-wrap justify-center sm:justify-start gap-4 sm:gap-6"
                    ghost-class="opacity-30"
                    :animation="250"
                    :delay="200"
                    :delayOnTouchOnly="true"
                >
                    <template #item="{ element }">
                        <div @click="addToSentence(element)" :data-id="element.id" :class="pictogramClasses">
                            <div v-if="isEyetrackerActive && !isCalibrating && hoveredPictogramId == element.id"
                                 class="absolute bottom-0 left-0 h-3 bg-green-500 transition-all duration-100 ease-linear pointer-events-none"
                                 :style="{ width: dwellProgress + '%' }">
                            </div>

                            <img v-if="element.image_path" :src="element.image_path" class="w-16 h-16 sm:w-20 sm:h-20 object-contain mb-3 rounded pointer-events-none" />
                            <div v-else class="text-5xl sm:text-6xl mb-3 pointer-events-none">🖼️</div>
                            <span :class="isCvi ? 'text-white' : 'text-gray-800'" class="text-lg sm:text-xl font-bold text-center leading-tight pointer-events-none">{{ element.name }}</span>
                        </div>
                    </template>
                </draggable>
            </div>
        </div>

        <div v-if="showPinModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div :class="[isCvi ? 'bg-black border-yellow-400' : 'bg-white border-gray-100', 'rounded-3xl p-8 max-w-sm w-full text-center border-4 shadow-2xl flex flex-col']">
                <div class="text-6xl mb-4 pointer-events-none">🔒</div>
                <h3 :class="[isCvi ? 'text-yellow-400' : 'text-gray-900', 'text-2xl font-black mb-6 pointer-events-none']">Blokada Rodzicielska</h3>
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

<style>
#webgazerGazeDot,
#webgazerVideoContainer,
#webgazerFaceOverlay,
#webgazerFaceFeedbackBox {
    pointer-events: none !important;
}
</style>
