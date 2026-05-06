<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    child: Object,
    pictograms: Array
});

const currentQuestionIndex = ref(0);
const score = ref(0);
const isFinished = ref(false);
const questions = ref([]);

const shuffle = (array) => array.sort(() => Math.random() - 0.5);

onMounted(() => {
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

const selectOption = (selectedPic) => {
    let currentQ = questions.value[currentQuestionIndex.value];

    if (selectedPic.id === currentQ.correct.id) {
        score.value++;
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

    <div class="min-h-screen bg-gray-50 flex flex-col items-center py-10 px-4">
        <div class="w-full max-w-3xl bg-white rounded-3xl shadow-xl overflow-hidden p-8">

            <div v-if="!isFinished && questions.length > 0">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Gdzie jest... ?</h2>
                    <span class="text-lg font-bold text-blue-600">Pytanie {{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
                </div>

                <div class="text-center mb-10">
                    <h1 class="text-5xl font-black text-gray-900 capitalize drop-shadow-sm">
                        "{{ questions[currentQuestionIndex].correct.name }}"
                    </h1>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <button
                        v-for="pic in questions[currentQuestionIndex].options"
                        :key="pic.id"
                        @click="selectOption(pic)"
                        class="p-6 border-4 border-gray-100 rounded-2xl hover:border-blue-500 hover:shadow-lg transition-all active:scale-95 bg-white flex flex-col items-center justify-center aspect-square"
                    >
                        <img :src="pic.image_path" :alt="pic.name" class="w-32 h-32 object-contain mb-4">
                    </button>
                </div>
            </div>

            <div v-if="isFinished" class="text-center py-10">
                <div class="text-7xl mb-6">🎉</div>
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Koniec treningu!</h2>
                <p class="text-2xl text-gray-600 mb-8">Twój wynik: <span class="font-bold text-green-600">{{ score }}</span> z {{ questions.length }}</p>

                <button
                    @click="submitScore"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl py-4 px-10 rounded-xl shadow-lg transition-all active:scale-95"
                >
                    Zapisz wynik i wróć
                </button>
            </div>

        </div>
    </div>
</template>
