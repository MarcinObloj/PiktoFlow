<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, nextTick, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';
import confetti from 'canvas-confetti';
import { useTTS } from '@/Composables/useTTS';
import Icon from '@/Components/Icon.vue';
import BaseModal from '@/Components/BaseModal.vue';

const props = defineProps({
    child: Object,
    categories: Array,
});

const { speak, stop, voices, selectedVoice, rate, pitch, volume } = useTTS();
const sentence = ref([]);
const localCategories = ref([...props.categories]);
const predictedPictograms = ref([]);
const isLoadingPredictions = ref(false);

const fetchPredictions = async () => {
    const lastId = sentence.value.length > 0 ? sentence.value[sentence.value.length - 1].id : null;
    const excludeIds = sentence.value.map(p => p.id).join(',');

    isLoadingPredictions.value = true;
    try {
        const response = await axios.get(route('children.predict', props.child.id), {
            params: {
                last_pictogram_id: lastId,
                exclude_ids: excludeIds
            }
        });
        predictedPictograms.value = response.data;
    } catch (error) {
        console.error("Błąd podczas pobierania predykcji", error);
    } finally {
        isLoadingPredictions.value = false;
    }
};

watch(sentence, () => {
    fetchPredictions();
}, { deep: true });

// The child board has its own theming (standard / CVI) and must NOT inherit the
// caregiver's dark-mode preference. Suspend the global `dark` class while mounted
// so the board and its exit modal stay visually consistent, then restore on leave.
let hadDarkClass = false;
onMounted(() => {
    if (typeof document !== 'undefined') {
        hadDarkClass = document.documentElement.classList.contains('dark');
        document.documentElement.classList.remove('dark');
    }
});
onBeforeUnmount(() => {
    if (hadDarkClass && typeof document !== 'undefined') {
        document.documentElement.classList.add('dark');
    }
});

onMounted(() => {
    rate.value = props.child.tts_rate || 0.9;
    pitch.value = props.child.tts_pitch || 1.0;
    volume.value = props.child.tts_volume || 1.0;

    if (props.child.tts_voice) {
        const timer = setInterval(() => {
            if (voices.value.length > 0) {
                const voice = voices.value.find(v => v.name === props.child.tts_voice);
                if (voice) selectedVoice.value = voice;
                clearInterval(timer);
            }
        }, 100);
    }

    fetchPredictions();
});

const isCvi = computed(() => props.child.is_cvi_mode);
const accent = computed(() => props.child.cvi_accent_color || '#facc15');

// CVI grid density controls spacing and pictogram size.
const density = computed(() => props.child.cvi_grid_density || 'normal');
const densityGap = computed(() => ({
    compact: 'gap-3 sm:gap-4',
    normal: 'gap-4 sm:gap-6',
    spacious: 'gap-6 sm:gap-10',
}[density.value] || 'gap-4 sm:gap-6'));
const densitySize = computed(() => ({
    compact: 'w-28 h-28 sm:w-32 sm:h-32',
    normal: 'w-36 h-36 sm:w-40 sm:h-40',
    spacious: 'w-44 h-44 sm:w-52 sm:h-52',
}[density.value] || 'w-36 h-36 sm:w-40 sm:h-40'));

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
        : densitySize.value + ' border-4 ';

    const theme = isCvi.value
        ? 'bg-black '
        : 'border-gray-200 bg-white hover:bg-gray-50 active:bg-blue-100 ';

    return base + size + theme;
});

// Dynamic accent styling for CVI elements (border + glow driven by chosen colour).
const cviTileStyle = computed(() => isCvi.value ? {
    borderColor: accent.value,
    boxShadow: `0 0 15px ${accent.value}80`,
} : {});

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
        let lastUpdate = 0;

        window.webgazer.setGazeListener((data, elapsedTime) => {
            if (data == null || isCalibrating.value) return;

            // Optymalizacja: Throttling do ~20 FPS (50ms), by nie obciążać Vue i DOM
            if (elapsedTime - lastUpdate < 50) return;
            lastUpdate = elapsedTime;

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

const ttsOptions = (extra = {}) => ({
    voice: selectedVoice.value?.name,
    rate: rate.value,
    pitch: pitch.value,
    volume: volume.value,
    ...extra,
});

const playPictogram = (pictogram) => {
    if (pictogram.audio_path) {
        const audio = new Audio(pictogram.audio_path);
        audio.volume = volume.value;
        audio.play();
    } else {
        speak(pictogram.name, ttsOptions());
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
    const colors = isCvi.value ? [accent.value, '#ffffff'] : ['#3b82f6', '#ef4444', '#10b981', '#f59e0b'];
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
            audio.volume = volume.value;
            audio.onended = () => playNext(index + 1);
            audio.play();
        } else {
            speak(p.name, ttsOptions({ onEnd: () => playNext(index + 1) }));
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

const isVerifyingPin = ref(false);

const verifyPin = async () => {
    if (isVerifyingPin.value) return;
    isVerifyingPin.value = true;
    pinError.value = '';

    try {
        const { data } = await axios.post(route('children.verify-pin', props.child.id), {
            pin: pin.value,
        });

        if (data.valid) {
            if (isEyetrackerActive.value) {
                window.webgazer.end();
            }
            router.get(route('children.index'));
        } else {
            pinError.value = 'Nieprawidłowy kod PIN.';
            pin.value = '';
        }
    } catch (error) {
        pinError.value = 'Nie udało się zweryfikować kodu. Spróbuj ponownie.';
        pin.value = '';
    } finally {
        isVerifyingPin.value = false;
    }
};
</script>

<template>
    <Head :title="'Tablica - ' + child.name" />

    <div :class="boardClasses">
        <!-- HEADER KONTROLNY (STICKY) -->
        <div class="sticky top-0 z-40 p-4 sm:p-6 lg:p-8 border-b transition-colors shadow-sm"
             :class="isCvi ? 'bg-black' : 'bg-white/95 backdrop-blur border-gray-100'"
             :style="isCvi ? { borderColor: accent } : {}">
            <div class="max-w-7xl mx-auto flex flex-col gap-6">
                <!-- PRZYCISKI -->
                <div class="flex flex-wrap justify-between items-center gap-4 w-full">
                    <h2 class="text-xl font-bold truncate hidden sm:block" :class="isCvi ? '' : 'text-gray-700'" :style="isCvi ? { color: accent } : {}">Tablica: {{ child.name }}</h2>
                    <div class="flex flex-wrap gap-2 sm:gap-4 ml-auto">
                        <button v-if="!isEyetrackerActive" disabled
                                :class="[isCvi ? 'bg-gray-900 text-gray-600 border-gray-800' : 'bg-gray-100 text-gray-400 border-gray-200']"
                                class="transition-colors flex items-center gap-2 px-4 sm:px-6 py-2 sm:py-3 rounded-2xl text-sm font-bold border shadow-sm cursor-not-allowed opacity-75"
                                title="Ze względu na ograniczenia sprzętowe standardowych kamer, funkcja w budowie.">
                            <Icon name="eye" :size="20" class="opacity-50" /> <span class="hidden sm:inline">Eyetracker (W budowie)</span>
                        </button>

                        <button v-else-if="isCalibrating" @click="finishCalibration"
                                :class="[isCvi ? 'text-black' : 'bg-green-500 text-white border-green-600 hover:bg-green-600']"
                                :style="isCvi ? { backgroundColor: accent, boxShadow: `0 0 15px ${accent}99` } : {}"
                                class="transition-colors flex items-center gap-2 px-4 sm:px-6 py-2 sm:py-3 rounded-2xl text-sm font-bold border shadow-md animate-pulse active:scale-95">
                            <Icon name="target" :size="20" /> <span class="hidden sm:inline">Zakończ kalibrację</span>
                        </button>

                        <button v-else @click="toggleEyetracker"
                                :class="[isCvi ? 'bg-red-900 text-white border-red-700' : 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100']"
                                class="transition-colors flex items-center gap-2 px-4 sm:px-6 py-2 sm:py-3 rounded-2xl text-sm font-bold border shadow-sm active:scale-95">
                            <Icon name="stop" :size="20" /> <span class="hidden sm:inline">Wyłącz Eyetracker</span>
                        </button>

                        <button @click="openPinModal"
                                :class="[isCvi ? 'bg-gray-900 text-white border-gray-700' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50']"
                                class="transition-colors flex items-center gap-2 px-4 sm:px-6 py-2 sm:py-3 rounded-2xl text-sm font-bold border shadow-sm active:scale-95">
                            <Icon name="lock" :size="20" /> Wyjdź
                        </button>
                    </div>
                </div>

                <!-- POLE BUDOWANIA ZDANIA -->
                <div :class="isCvi ? 'bg-black border-4' : 'bg-blue-50 border-4 border-blue-200 shadow-sm'"
                     :style="isCvi ? { borderColor: accent, boxShadow: `0 0 15px ${accent}4d` } : {}"
                     class="rounded-3xl p-4 transition-all flex flex-col gap-4">
                    <div class="flex items-center gap-4 min-h-[120px]">
                        <div :class="isCvi ? 'bg-black' : 'bg-white border-blue-200'"
                             :style="isCvi ? { borderColor: `${accent}80` } : {}"
                             class="flex-1 flex flex-wrap justify-center sm:justify-start gap-2 p-4 rounded-2xl border-2 border-dashed min-h-[120px] items-center text-gray-400">
                            <span v-if="sentence.length === 0" class="text-lg font-medium text-center w-full sm:w-auto">Ułóż zdanie klikając w obrazki...</span>

                            <TransitionGroup
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 scale-75"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-75"
                                move-class="transition duration-200"
                            >
                                <button v-for="(p, index) in sentence" :key="p.id + '-' + index"
                                        @click="sentence.splice(index, 1)"
                                        :aria-label="'Usuń ' + p.name + ' ze zdania'"
                                        :class="isCvi ? 'bg-black' : 'border-gray-200 bg-white'"
                                        :style="isCvi ? { borderColor: accent } : {}"
                                        class="border-2 rounded-xl p-2 w-24 h-24 flex flex-col items-center justify-center shadow-sm hover:bg-red-50 cursor-pointer transition-transform hover:scale-105 active:scale-95 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                    <img v-if="p.image_path" :src="p.image_path" :alt="p.name" loading="lazy" class="w-10 h-10 object-contain mb-1 rounded pointer-events-none" />
                                    <span :class="isCvi ? 'text-white' : 'text-gray-800'" class="text-xs font-bold text-center truncate w-full pointer-events-none">{{ p.name }}</span>
                                </button>
                            </TransitionGroup>
                        </div>

                        <div class="flex flex-col gap-3">
                            <button @click="speakSentence"
                                    aria-label="Odczytaj zdanie"
                                    :class="isCvi ? 'text-black' : 'bg-blue-500 text-white hover:bg-blue-600'"
                                    :style="isCvi ? { backgroundColor: accent } : {}"
                                    class="eyetracker-target p-4 rounded-2xl shadow-md h-[56px] w-[56px] flex items-center justify-center transition-all active:scale-90 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                    data-id="speak-btn">
                                <Icon name="volume" :size="26" />
                            </button>
                            <button @click="clearSentence"
                                    aria-label="Wyczyść zdanie"
                                    :class="isCvi ? 'bg-gray-800 text-white hover:bg-gray-700' : 'bg-red-500 text-white hover:bg-red-600'"
                                    class="eyetracker-target p-4 rounded-2xl shadow-md h-[56px] w-[56px] flex items-center justify-center transition-all active:scale-90 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                    data-id="clear-btn">
                                <Icon name="trash" :size="26" />
                            </button>
                        </div>
                    </div>

                    <!-- PODPOWIEDZI -->
                    <div v-if="isLoadingPredictions || predictedPictograms.length > 0" class="flex flex-wrap items-center gap-3 justify-center sm:justify-start px-2 py-2 border-t border-gray-200/50 mt-2">
                        <span :class="isCvi ? '' : 'text-gray-500'" :style="isCvi ? { color: accent } : {}" class="text-sm font-bold flex items-center gap-1">
                            <Icon name="sparkles" :size="16" /> Podpowiedzi:
                        </span>

                        <template v-if="isLoadingPredictions">
                            <div v-for="n in 4" :key="'sk-' + n"
                                 class="h-9 w-24 rounded-xl animate-pulse"
                                 :class="isCvi ? 'bg-gray-800' : 'bg-gray-200'"></div>
                        </template>

                        <TransitionGroup v-else
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <button v-for="p in predictedPictograms" :key="'pred-'+p.id"
                                    @click="addToSentence(p)"
                                    :aria-label="'Dodaj podpowiedź: ' + p.name"
                                    :class="isCvi ? 'bg-black text-white' : 'bg-purple-50 hover:bg-purple-100 border-purple-200 text-purple-900'"
                                    :style="isCvi ? { borderColor: accent } : {}"
                                    class="eyetracker-target flex items-center gap-2 border-2 rounded-xl px-4 py-2 transition-all active:scale-95 shadow-sm focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-purple-500 focus-visible:ring-offset-2"
                                    :data-id="p.id">
                                <img v-if="p.image_path" :src="p.image_path" :alt="p.name" loading="lazy" class="w-6 h-6 object-contain pointer-events-none" />
                                <span class="font-bold text-sm pointer-events-none">{{ p.name }}</span>
                            </button>
                        </TransitionGroup>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8">
            <div v-for="category in localCategories" :key="category.id" class="mb-12 text-center sm:text-left">
                <h3 :class="isCvi ? 'font-black' : 'font-bold text-gray-700'"
                    class="text-2xl mb-6 uppercase tracking-wider px-2"
                    :style="isCvi ? { color: accent } : { color: category.color }">
                    {{ category.name }}
                </h3>

                <draggable
                    v-model="category.pictograms"
                    @end="handleDragEnd(category.id)"
                    item-key="id"
                    class="flex flex-wrap justify-center sm:justify-start"
                    :class="densityGap"
                    ghost-class="opacity-30"
                    :animation="250"
                    :delay="200"
                    :delayOnTouchOnly="true"
                >
                    <template #item="{ element }">
                        <button
                            @click="addToSentence(element)"
                            @keydown.enter.prevent="addToSentence(element)"
                            @keydown.space.prevent="addToSentence(element)"
                            :data-id="element.id"
                            :aria-label="element.name"
                            :style="cviTileStyle"
                            :class="[
                                pictogramClasses,
                                isCvi
                                    ? 'focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-yellow-400 focus-visible:ring-offset-4 focus-visible:ring-offset-black'
                                    : 'focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-500 focus-visible:ring-offset-2'
                            ]"
                        >
                            <div v-if="isEyetrackerActive && !isCalibrating && hoveredPictogramId == element.id"
                                 class="absolute bottom-0 left-0 h-3 bg-green-500 transition-all duration-100 ease-linear pointer-events-none"
                                 :style="{ width: dwellProgress + '%' }">
                            </div>

                            <img v-if="element.image_path" :src="element.image_path" :alt="element.name" loading="lazy" class="w-16 h-16 sm:w-20 sm:h-20 object-contain mb-3 rounded pointer-events-none" />
                            <div v-else class="mb-3 pointer-events-none text-gray-300"><Icon name="image" :size="56" /></div>
                            <span :class="isCvi ? 'text-white' : 'text-gray-800'" class="text-lg sm:text-xl font-bold text-center leading-tight pointer-events-none">{{ element.name }}</span>
                        </button>
                    </template>
                </draggable>
            </div>
        </div>

        <BaseModal :show="showPinModal" max-width="sm" @close="closePinModal">
            <div class="p-8 text-center" :class="isCvi ? 'bg-black rounded-card border-4' : ''" :style="isCvi ? { borderColor: accent } : {}">
                <div class="mx-auto mb-4 w-16 h-16 rounded-2xl flex items-center justify-center"
                     :class="isCvi ? 'text-black' : 'bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300'"
                     :style="isCvi ? { backgroundColor: accent } : {}">
                    <Icon name="lock" :size="32" />
                </div>
                <h3 :class="isCvi ? '' : 'text-gray-900 dark:text-white'" :style="isCvi ? { color: accent } : {}" class="text-2xl font-black mb-6">Blokada Rodzicielska</h3>
                <input ref="pinInputRef" v-model="pin" type="password" maxlength="4" inputmode="numeric"
                       :class="isCvi ? 'bg-black text-white' : 'bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white'"
                       :style="isCvi ? { borderColor: accent, color: accent } : {}"
                       class="w-full text-center text-4xl tracking-[0.5em] py-4 rounded-xl border-2 outline-none mb-6"
                       @keyup.enter="verifyPin" />
                <p v-if="pinError" class="text-red-500 text-sm mb-4">{{ pinError }}</p>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <button @click="closePinModal" class="py-4 font-bold rounded-xl transition-colors focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-gray-500 focus-visible:ring-offset-2"
                            :class="isCvi ? 'bg-gray-900 text-white border-2 border-gray-600 hover:bg-gray-800' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 hover:bg-gray-300'">Anuluj</button>
                    <button @click="verifyPin" :disabled="isVerifyingPin"
                            :class="isCvi ? 'text-black' : 'bg-primary-600 text-white hover:bg-primary-700'"
                            :style="isCvi ? { backgroundColor: accent } : {}"
                            class="py-4 font-bold rounded-xl transition-colors focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:opacity-50">Odblokuj</button>
                </div>
            </div>
        </BaseModal>
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
