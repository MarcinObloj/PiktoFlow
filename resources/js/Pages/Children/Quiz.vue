<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useTTS } from '@/Composables/useTTS';

const props = defineProps({
    child: Object,
    pictograms: Array
});

const { speak, loadVoices } = useTTS();

const currentQuestionIndex = ref(0);
const score = ref(0);
const isFinished = ref(false);
const questions = ref([]);

const shuffle = (array) => array.sort(() => Math.random() - 0.5);

onMounted(() => {
    loadVoices();

    for (let i = 0; i < 5; i++) {
        if (props.pictograms.length < 4) break;

        let shuffledPics = shuffle([...props.pictograms]).slice(0, 4);
        let correctPic = shuffledPics[Math.floor(Math.random() * 4)];

        questions.value.push({
            correct: correctPic,
            options: shuffledPics
        });
    }

    if (questions.value.length === 0) {
        alert("Wymagane minimum 4 piktogramy na tablicy, aby zagrać w quiz.");
        router.visit(route('children.index'));
    }
});

const handleHover = (text) => {
    if (!text) return;
    const settings = {
        voice: props.child?.tts_voice || null,
        rate: props.child?.tts_rate || 1,
        pitch: props.child?.tts_pitch || 1,
        volume: props.child?.tts_volume || 1
    };

    speak(text, settings);
};

const selectOption = (selectedPic) => {
    let currentQ = questions.value[currentQuestionIndex.value];

    if (selectedPic.id === currentQ.correct.id) {
        score.value++;
        handleHover("Brawo!");
    } else {
        handleHover("Spróbuj jeszcze raz");
    }

    if (currentQuestionIndex.value + 1 < questions.value.length) {
        currentQuestionIndex.value++;
    } else {
        isFinished.value = true;
    }
};

const submitScore = () => {
    router.post(route('children.quiz.save', props.child.id), {
        score: score.value,
        total: questions.value.length
    });
};
</script>

<template>
    <Head title="Trening Słownictwa" />

    <div class="min-h-screen bg-gray-50 flex flex-col items-center py-10 px-4 font-sans">
        <div class="w-full max-w-3xl bg-white rounded-3xl shadow-xl overflow-hidden p-8 border-4 border-blue-100">

            <div v-if="!isFinished && questions.length > 0">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-700">Gdzie jest... ?</h2>
                    <div class="px-4 py-2 bg-blue-100 rounded-full">
                        <span class="text-lg font-bold text-blue-700">Pytanie {{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
                    </div>
                </div>

                <div class="text-center mb-10">
                    <h1 class="text-6xl font-black text-gray-900 capitalize tracking-tight">
                        {{ questions[currentQuestionIndex].correct.name }}
                    </h1>
                    <button
                        @click="handleHover(questions[currentQuestionIndex].correct.name)"
                        class="mt-4 text-blue-500 hover:text-blue-700 transition flex items-center gap-2 mx-auto font-bold"
                    >
                        <span>🔊 Powtórz słowo</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <button
                        v-for="pic in questions[currentQuestionIndex].options"
                        :key="pic.id"
                        @click="selectOption(pic)"
                        @mouseenter="handleHover(pic.name)"
                        class="p-6 border-4 border-gray-100 rounded-[2rem] hover:border-blue-500 hover:bg-blue-50 hover:shadow-2xl transition-all active:scale-95 bg-white flex flex-col items-center justify-center aspect-square group"
                    >
                        <img :src="pic.image_path" :alt="pic.name" class="w-40 h-40 object-contain mb-4 group-hover:scale-110 transition-transform">
                        <span class="text-gray-400 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Słuchaj</span>
                    </button>
                </div>
            </div>

            <div v-if="isFinished" class="text-center py-10">
                <div class="text-9xl mb-6">🏆</div>
                <h2 class="text-5xl font-black text-gray-900 mb-4">Wspaniale!</h2>
                <p class="text-2xl text-gray-600 mb-10">
                    Twój wynik to <span class="font-black text-green-600">{{ score }}</span> na {{ questions.length }} piktogramów.
                </p>

                <button
                    @click="submitScore"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-black text-2xl py-6 px-10 rounded-2xl shadow-lg hover:shadow-green-200 transition-all active:scale-95 border-b-8 border-green-700"
                >
                    Zapisz i wróć do profilu
                </button>
            </div>

        </div>
    </div>
</template>

<style scoped>

.shadow-xl {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
}
</style>
