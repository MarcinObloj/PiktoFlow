<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';

const props = defineProps({
    child: Object,
    categories: Array,
});

const localCategories = ref([...props.categories]);

const sentence = ref([]);

const handleDragEnd = (categoryId) => {
    const category = localCategories.value.find(c => c.id === categoryId);
    const ids = category.pictograms.map(p => p.id);

    axios.post(route('children.reorder', props.child.id), { ids })
        .then(() => {
            console.log(`Kolejność w kategorii ${category.name} zapisana.`);
        })
        .catch(err => console.error("Błąd zapisu kolejności:", err));
};

const speak = (text) => {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'pl-PL';
        utterance.rate = 0.9;
        window.speechSynthesis.speak(utterance);
    }
};

const addToSentence = (pictogram) => {
    sentence.value.push(pictogram);
    speak(pictogram.name);

    axios.post(route('children.log-click', props.child.id), {
        pictogram_id: pictogram.id
    }).catch(error => {
        console.error("Nie udało się zapisać statystyki kliknięcia", error);
    });
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
</script>

<template>
    <Head :title="'Tablica - ' + child.name" />

    <div class="min-h-screen bg-white flex flex-col relative font-sans">
        <div class="absolute top-4 right-4 z-40">
            <button @click="openPinModal" class="text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-1 bg-gray-50 px-3 py-2 rounded-full text-sm font-bold border border-gray-100 shadow-sm">
                <span>🔒</span> Wyjdź
            </button>
        </div>

        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8 pt-16">
            <div class="bg-blue-50 border-4 border-blue-200 rounded-3xl p-4 mb-8 shadow-sm">
                <div class="flex items-center gap-4 min-h-[120px]">
                    <div class="flex-1 flex flex-wrap gap-2 p-4 bg-white rounded-2xl border-2 border-dashed border-blue-200 min-h-[120px] items-center text-gray-400">
                        <span v-if="sentence.length === 0">Ułóż zdanie klikając w obrazki...</span>
                        <div v-for="(p, index) in sentence" :key="index" @click="sentence.splice(index, 1)" class="border-2 border-gray-200 rounded-xl p-2 w-24 h-24 flex flex-col items-center justify-center bg-white shadow-sm hover:bg-red-50 cursor-pointer">
                            <img v-if="p.image_path" :src="p.image_path" class="w-10 h-10 object-contain mb-1 rounded" />
                            <span class="text-xs font-bold text-gray-800 text-center truncate w-full">{{ p.name }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <button @click="speakSentence" class="bg-blue-500 text-white p-4 rounded-2xl shadow-md h-16 w-16 active:scale-95 text-2xl">🔊</button>
                        <button @click="clearSentence" class="bg-red-500 text-white p-4 rounded-2xl shadow-md h-16 w-16 active:scale-95 text-2xl">🗑️</button>
                    </div>
                </div>
            </div>

            <div v-for="category in localCategories" :key="category.id" class="mb-10">
                <h3 class="text-xl font-bold mb-4 uppercase tracking-wider px-2" :style="{ color: category.color }">
                    {{ category.name }}
                </h3>

                <draggable
                    v-model="category.pictograms"
                    @end="handleDragEnd(category.id)"
                    item-key="id"
                    class="flex flex-wrap gap-4"
                    ghost-class="opacity-30"
                    :animation="250"
                >
                    <template #item="{ element }">
                        <div @click="addToSentence(element)"
                             class="border-4 rounded-3xl p-4 w-36 h-36 sm:w-40 sm:h-40 flex flex-col items-center justify-center cursor-move hover:bg-gray-50 active:bg-blue-100 active:scale-95 transition-all shadow-sm bg-white border-gray-200">

                            <img v-if="element.image_path" :src="element.image_path" class="w-16 h-16 sm:w-20 sm:h-20 object-contain mb-2 rounded" />
                            <div v-else class="text-5xl sm:text-6xl mb-2">🖼️</div>
                            <span class="text-lg sm:text-xl font-bold text-gray-800 text-center leading-tight">{{ element.name }}</span>
                        </div>
                    </template>
                </draggable>
            </div>
        </div>

        <div v-if="showPinModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full shadow-2xl text-center border-4 border-gray-100">
                <div class="text-6xl mb-4">🔒</div>
                <h3 class="text-2xl font-black mb-2">Blokada Rodzicielska</h3>
                <input ref="pinInputRef" v-model="pin" type="password" maxlength="4" class="w-full text-center text-4xl tracking-[0.5em] py-4 rounded-2xl border-2 border-gray-200 outline-none mb-2" @keyup.enter="verifyPin" />
                <p class="text-red-500 text-sm h-6">{{ pinError }}</p>
                <div class="flex gap-3 mt-6">
                    <button @click="closePinModal" class="flex-1 py-4 bg-gray-100 font-bold rounded-2xl">Anuluj</button>
                    <button @click="verifyPin" class="flex-1 py-4 bg-blue-600 text-white font-bold rounded-2xl">Odblokuj</button>
                </div>
            </div>
        </div>
    </div>
</template>
