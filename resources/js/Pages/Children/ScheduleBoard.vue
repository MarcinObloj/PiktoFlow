<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, nextTick, computed } from 'vue';

const props = defineProps({
    child: Object,
    planPictograms: Array,
});

const isCvi = computed(() => props.child.is_cvi_mode);

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
};

const verifyPin = () => {
    if (pin.value === '1234') {
        router.get(route('children.index'));
    } else {
        pinError.value = 'Nieprawidłowy kod PIN.';
        pin.value = '';
    }
};

const getLabel = (index, total) => {
    if (index === 0) return 'Najpierw';
    if (index === total - 1) return 'Potem';
    return 'Następnie';
};
</script>

<template>
    <Head :title="'Plan Dnia - ' + child.name" />

    <div :class="[isCvi ? 'bg-black text-yellow-400' : 'bg-green-50 text-gray-900', 'min-h-screen flex flex-col relative font-sans transition-colors']">

        <button @click="openPinModal" :class="[isCvi ? 'bg-black border-yellow-400 text-yellow-400 hover:bg-yellow-400 hover:text-black' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-100', 'absolute top-4 right-4 z-40 px-6 py-3 rounded-full font-bold border-2 shadow-sm transition']">
            🔒 Wyjdź
        </button>

        <div class="flex-1 flex flex-col items-center justify-center p-4 sm:p-8">
            <h1 :class="[isCvi ? 'text-yellow-400' : 'text-green-700', 'text-4xl sm:text-5xl lg:text-6xl font-black mb-12 lg:mb-16 text-center uppercase tracking-widest']">Plan Dnia</h1>

            <div v-if="planPictograms.length === 0" class="text-2xl font-bold opacity-50 text-center">
                Brak przypisanego planu na dziś.
            </div>

            <div v-else class="flex flex-col md:flex-row flex-wrap items-center justify-center gap-6 md:gap-4 lg:gap-6 w-full max-w-7xl px-2">

                <template v-for="(pic, index) in planPictograms" :key="pic.id">

                    <div @click="playPictogram(pic)" :class="[isCvi ? 'bg-black border-yellow-400 shadow-[0_0_30px_rgba(250,204,21,0.5)]' : 'bg-white border-green-500 shadow-xl', 'border-8 rounded-[2.5rem] p-6 w-56 h-56 md:w-48 md:h-48 lg:w-64 lg:h-64 flex flex-col items-center justify-center cursor-pointer active:scale-95 transition-all relative group shrink-0']">

                        <div :class="[isCvi ? 'bg-yellow-400 text-black' : 'bg-green-600 text-white', 'absolute -top-5 px-6 py-1.5 rounded-full font-black text-lg md:text-base lg:text-xl uppercase tracking-wider shadow-md whitespace-nowrap']">
                            {{ getLabel(index, planPictograms.length) }}
                        </div>

                        <img v-if="pic.image_path" :src="pic.image_path" class="w-28 h-28 md:w-20 md:h-20 lg:w-32 lg:h-32 object-contain mb-3 rounded" />
                        <span :class="[isCvi ? 'text-yellow-400' : 'text-gray-900', 'text-2xl md:text-xl lg:text-3xl font-black text-center leading-tight truncate w-full px-2']">{{ pic.name }}</span>
                    </div>

                    <div v-if="index < planPictograms.length - 1" :class="[isCvi ? 'text-yellow-400' : 'text-green-500', 'text-5xl md:text-4xl lg:text-6xl font-black rotate-90 md:rotate-0 my-2 md:my-0 shrink-0']">
                        →
                    </div>

                </template>
            </div>
        </div>

        <div v-if="showPinModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div :class="[isCvi ? 'bg-black border-yellow-400' : 'bg-white border-gray-100', 'rounded-3xl p-8 max-w-sm w-full text-center border-4 shadow-2xl flex flex-col']">
                <div class="text-6xl mb-4">🔒</div>
                <h3 :class="[isCvi ? 'text-yellow-400' : 'text-gray-900', 'text-2xl font-black mb-6']">Blokada</h3>
                <input ref="pinInputRef" v-model="pin" type="password" maxlength="4" :class="[isCvi ? 'bg-black border-yellow-400 text-yellow-400 placeholder-yellow-700' : 'bg-gray-50 border-gray-300 text-gray-900', 'w-full text-center text-4xl tracking-[0.5em] py-4 rounded-xl border-2 outline-none mb-6']" @keyup.enter="verifyPin" />
                <p v-if="pinError" class="text-red-500 text-sm mb-4">{{ pinError }}</p>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <button @click="closePinModal" :class="[isCvi ? 'bg-gray-900 text-yellow-500 border border-yellow-500' : 'bg-gray-200 text-gray-800 hover:bg-gray-300', 'py-4 font-bold rounded-xl transition']">Anuluj</button>
                    <button @click="verifyPin" :class="[isCvi ? 'bg-yellow-400 text-black hover:bg-yellow-500' : 'bg-green-600 text-white hover:bg-green-700', 'py-4 font-bold rounded-xl transition']">Wyjdź</button>
                </div>
            </div>
        </div>

    </div>
</template>
