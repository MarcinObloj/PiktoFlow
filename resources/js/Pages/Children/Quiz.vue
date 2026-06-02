<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useTTS } from '@/Composables/useTTS';

const props = defineProps({ child: Object, pictograms: Array });
const { speak, loadVoices } = useTTS();

const gameMode = ref(null);
const currentQuestionIndex = ref(0);
const score = ref(0);
const isFinished = ref(false);
const questions = ref([]);
const showErrorModal = ref(false);
const showExitModal = ref(false);

const currentSentence = ref([]);
const finishedSentencesLengths = ref([]);
const correctIds = ref([]);

const shuffle = (array) => [...array].sort(() => Math.random() - 0.5);

const startQuiz = (mode) => {
    gameMode.value = mode;
    questions.value = [];

    if (mode === 'words') {
        generateWordQuestions();
    } else {
        generateSentenceQuestions();
    }
};

const generateWordQuestions = () => {
    for (let i = 0; i < 5; i++) {
        let shuffled = shuffle(props.pictograms).slice(0, 4);
        questions.value.push({
            correct: shuffled[0],
            options: shuffle(shuffled)
        });
    }
};

const generateSentenceQuestions = () => {
    for (let i = 0; i < 5; i++) {
        let target = shuffle(props.pictograms).slice(0, 3);
        questions.value.push({
            target: target,
            options: shuffle(target)
        });
    }
};

const handleHover = (text) => {
    if (!text) return;
    speak(text, {
        voice: props.child?.tts_voice,
        rate: props.child?.tts_rate || 1
    });
};

const selectWord = (selectedPic) => {
    if (selectedPic.id === questions.value[currentQuestionIndex.value].correct.id) {
        score.value++;
        correctIds.value.push(selectedPic.id);
        handleHover("Brawo!");
    } else {
        handleHover("Spróbuj jeszcze raz");
    }
    setTimeout(() => {
        nextQuestion();
    }, 500);
};

const addToSentence = (pic) => {
    currentSentence.value.push(pic);
    handleHover(pic.name);

    if (currentSentence.value.length === questions.value[currentQuestionIndex.value].target.length) {
        checkSentence();
    }
};

const checkSentence = () => {
    const target = questions.value[currentQuestionIndex.value].target;
    const isCorrect = currentSentence.value.every((p, i) => p.id === target[i].id);

    if (isCorrect) {
        score.value++;
        finishedSentencesLengths.value.push(target.length);
        currentSentence.value.forEach(p => correctIds.value.push(p.id));
        handleHover("Świetne zdanie!");
    } else {
        handleHover("Spróbuj jeszcze raz");
    }

    setTimeout(() => {
        currentSentence.value = [];
        nextQuestion();
    }, 1000);
};

const nextQuestion = () => {
    if (currentQuestionIndex.value + 1 < questions.value.length) {
        currentQuestionIndex.value++;
    } else {
        isFinished.value = true;
    }
};

const submitScore = () => {
    router.post(route('children.quiz.save', props.child.id), {
        score: score.value,
        total: questions.value.length,
        clicked_ids: correctIds.value,
        sentences: finishedSentencesLengths.value
    });
};

const exitQuiz = () => {
    showExitModal.value = true;
};

const confirmExit = () => {
    router.visit(route('children.index'));
};

const cancelExit = () => {
    showExitModal.value = false;
};

const goBack = () => {
    router.visit(route('children.index'));
};

onMounted(() => {
    loadVoices();
    if (!props.pictograms || props.pictograms.length < 4) showErrorModal.value = true;
});
</script>

<template>
    <Head title="Trening" />
    <div class="min-h-screen bg-blue-50 flex flex-col items-center py-10 px-4">

        <div v-if="showErrorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-8 text-center border-4 border-red-100">
                <div class="text-7xl mb-4">🧩</div>
                <h2 class="text-3xl font-black text-gray-900 mb-4">Mało piktogramów!</h2>
                <p class="text-gray-600 text-lg mb-8 leading-relaxed">
                    Aby uruchomić quiz, Twoja tablica musi zawierać przynajmniej <b>4 piktogramy</b>.
                </p>
                <button @click="goBack" class="w-full bg-gray-900 text-white font-bold text-xl py-4 rounded-2xl hover:bg-gray-800 transition shadow-lg">
                    Rozumiem, wracam
                </button>
            </div>
        </div>

        <div v-if="showExitModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-8 text-center border-4 border-yellow-200">
                <div class="text-7xl mb-4">🚪</div>
                <h2 class="text-3xl font-black text-gray-900 mb-4">Chcesz wyjść?</h2>
                <p class="text-gray-600 text-lg mb-8 leading-relaxed">
                    Postępy z tej sesji nie zostaną zapisane. Czy na pewno chcesz przerwać trening?
                </p>
                <div class="flex gap-4">
                    <button @click="cancelExit" class="px-4 w-1/2 bg-gray-100 text-gray-700 font-bold text-xl py-4 rounded-2xl hover:bg-gray-200 transition active:scale-95">
                        Zostaję
                    </button>
                    <button @click="confirmExit" class="px-4 w-1/2 bg-red-500 text-white font-bold text-xl py-4 rounded-2xl hover:bg-red-600 transition shadow-lg active:scale-95">
                        Wyjdź
                    </button>
                </div>
            </div>
        </div>

        <div v-if="!gameMode && !showErrorModal && !showExitModal" class="w-full max-w-2xl text-center space-y-8 mt-20 relative">
            <button @click="goBack" class="absolute -top-16 left-0 text-gray-500 hover:text-gray-800 font-bold flex items-center gap-2">
                ⬅ Wróć do profilu
            </button>
            <h1 class="text-5xl font-black text-gray-900 mb-10">Wybierz poziom zabawy</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <button @click="startQuiz('words')" class="bg-white p-10 rounded-[3rem] shadow-xl hover:scale-105 transition border-8 border-green-400 group">
                    <div class="text-7xl mb-4">🍎</div>
                    <h2 class="text-3xl font-bold">Poziom 1</h2>
                    <p class="text-gray-500">Pojedyncze słowa</p>
                </button>
                <button @click="startQuiz('sentences')" class="bg-white p-10 rounded-[3rem] shadow-xl hover:scale-105 transition border-8 border-purple-400 group">
                    <div class="text-7xl mb-4">💬</div>
                    <h2 class="text-3xl font-bold">Poziom 2</h2>
                    <p class="text-gray-500">Budowanie zdań</p>
                </button>
            </div>
        </div>

        <div v-if="gameMode === 'words' && !isFinished" class="w-full max-w-3xl bg-white rounded-[3rem] shadow-2xl p-8 border-4 border-blue-200 relative">
            <div class="flex justify-between items-center mb-8">
                <button @click="exitQuiz" class="px-4 flex items-center gap-2 px-5 py-3 bg-red-100 text-red-700 hover:bg-red-200 rounded-2xl font-bold transition shadow-sm">
                    ✖ Wyjdź
                </button>
                <div class="px-8 py-3 bg-blue-100 rounded-2xl shadow-sm">
                    <span class="text-lg font-bold text-blue-700">Pytanie {{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
                </div>
            </div>

            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-gray-500 mb-2">Gdzie jest... ?</h2>
                <h1
                    @mouseenter="handleHover(questions[currentQuestionIndex].correct.name)"
                    @click="handleHover(questions[currentQuestionIndex].correct.name)"
                    class="text-6xl font-black text-gray-900 capitalize tracking-tight cursor-pointer hover:text-blue-600 transition inline-block px-4 py-2 rounded-xl hover:bg-blue-50"
                    title="Kliknij lub najedź, aby posłuchać"
                >
                    {{ questions[currentQuestionIndex].correct.name }}
                </h1>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <button v-for="pic in questions[currentQuestionIndex].options" :key="pic.id" @click="selectWord(pic)" @mouseenter="handleHover(pic.name)" class="p-6 border-4 border-gray-100 rounded-[2rem] hover:border-green-400 hover:shadow-lg bg-white transition-all active:scale-95 flex flex-col items-center justify-center aspect-square">
                    <img :src="pic.image_path" class="w-40 h-40 object-contain">
                </button>
            </div>
        </div>

        <div v-if="gameMode === 'sentences' && !isFinished" class="w-full max-w-4xl bg-white rounded-[3rem] shadow-2xl p-8 border-4 border-purple-200 relative">
            <div class="flex justify-between items-center mb-8">
                <button @click="exitQuiz" class="flex items-center gap-2 px-5 py-3 bg-red-100 text-red-700 hover:bg-red-200 rounded-2xl font-bold transition shadow-sm">
                    ✖ Wyjdź
                </button>
                <div class="px-5 py-3 bg-purple-100 rounded-2xl shadow-sm">
                    <span class="text-lg font-bold text-purple-700">Zadanie {{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
                </div>
            </div>

            <h2 class="text-center text-3xl font-black mb-6 text-gray-700">Ułóż zdanie:</h2>

            <div class="text-center text-5xl font-black text-purple-600 mb-10 flex justify-center flex-wrap gap-x-6 gap-y-4">
                <span
                    v-for="p in questions[currentQuestionIndex].target"
                    :key="p.id"
                    @mouseenter="handleHover(p.name)"
                    @click="handleHover(p.name)"
                    class="underline decoration-4 cursor-pointer hover:text-purple-400 hover:bg-purple-50 px-2 rounded-xl transition"
                    title="Posłuchaj słowa"
                >
                    {{ p.name }}
                </span>
            </div>

            <div class="flex justify-center gap-4 mb-12 min-h-[150px] p-4 bg-purple-50 rounded-2xl border-4 border-dotted border-purple-200">
                <div v-for="(p, index) in currentSentence" :key="index" class="bg-white p-4 rounded-xl shadow-md border-2 border-purple-100">
                    <img :src="p.image_path" class="w-20 h-20 object-contain">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <button v-for="pic in questions[currentQuestionIndex].options" :key="pic.id" @click="addToSentence(pic)" @mouseenter="handleHover(pic.name)" class="p-4 border-4 border-gray-100 rounded-2xl hover:border-purple-400 bg-white transition-all active:scale-95 flex justify-center">
                    <img :src="pic.image_path" class="w-24 h-24 object-contain">
                </button>
            </div>
        </div>

        <div v-if="isFinished" class="text-center bg-white p-12 rounded-[3rem] shadow-2xl border-4 border-green-100">
            <div class="text-9xl mb-6 animate-bounce">🌟</div>
            <h2 class="text-5xl font-black mb-4 text-gray-900">Brawo!</h2>
            <p class="text-2xl mb-10 text-gray-600">Trening zakończony pomyślnie.</p>
            <button @click="submitScore" class="bg-green-500 hover:bg-green-600 text-white text-2xl font-black py-6 px-12 rounded-2xl border-b-8 border-green-700 active:translate-y-2 active:border-b-0 transition-all shadow-xl">
                ZAPISZ MOJE POSTĘPY
            </button>
        </div>
    </div>
</template>
