<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    category_id: '',
    image: null,
    audio: null,
});

const isRecording = ref(false);
const audioUrl = ref(null);
let mediaRecorder = null;
let audioChunks = [];
const isFakeLoading = ref(false);

const startRecording = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];

        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                audioChunks.push(event.data);
            }
        };

        mediaRecorder.onstop = () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            audioUrl.value = URL.createObjectURL(audioBlob);

            const file = new File([audioBlob], "nagranie_z_mikrofonu.webm", { type: 'audio/webm' });
            form.audio = file;

            stream.getTracks().forEach(track => track.stop());
        };

        mediaRecorder.start();
        isRecording.value = true;
    } catch (err) {
        console.error("Błąd mikrofonu:", err);
        alert("Nie udało się połączyć z mikrofonem. Upewnij się, że dałeś przeglądarce uprawnienia!");
    }
};

const stopRecording = () => {
    if (mediaRecorder && isRecording.value) {
        mediaRecorder.stop();
        isRecording.value = false;
    }
};

const clearRecording = () => {
    audioUrl.value = null;
    form.audio = null;
    audioChunks = [];
};

const submit = () => {
    isFakeLoading.value = true;
    setTimeout(() => {
        form.post(route('pictograms.store'), {
            onFinish: () => {
                isFakeLoading.value = false;
            }
        });
    }, 1500);
};
</script>

<template>
    <Head title="Dodaj Piktogram" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                🖼️ Dodaj nowy piktogram
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 relative">
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl p-8 border border-gray-100">
                    <form @submit.prevent="submit" class="flex flex-col gap-6">

                        <div>
                            <label class="block font-bold text-gray-700 text-lg mb-2">Nazwa piktogramu</label>
                            <input type="text" v-model="form.name" placeholder="np. Mój Pies, Ulubiona Zabawka..." class="block w-full rounded-2xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4" required />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-2 font-medium">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 text-lg mb-2">Kategoria</label>
                            <select v-model="form.category_id" class="block w-full rounded-2xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4" required>
                                <option disabled value="">Wybierz z listy...</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-2 font-medium">{{ form.errors.category_id }}</div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-2xl border-2 border-dashed border-gray-200">
                            <label class="block font-bold text-gray-700 text-lg mb-2">Zdjęcie (Wymagane)</label>
                            <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="block w-full text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors" required />
                            <div v-if="form.errors.image" class="text-red-500 text-sm mt-2 font-medium">{{ form.errors.image }}</div>
                        </div>

                        <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                            <label class="block font-bold text-blue-900 text-lg mb-4 flex items-center gap-2">
                                🎤 Własne nagranie głosowe <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded-full uppercase tracking-wider">Opcjonalnie</span>
                            </label>

                            <div class="flex flex-col gap-4">
                                <div v-if="!audioUrl" class="flex flex-wrap items-center gap-4">
                                    <button v-if="!isRecording" @click.prevent="startRecording" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-full flex items-center gap-2 transition active:scale-95 shadow-md">
                                        <span class="w-3 h-3 bg-white rounded-full"></span> Nagraj głos
                                    </button>

                                    <button v-else @click.prevent="stopRecording" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-6 rounded-full flex items-center gap-2 transition active:scale-95 shadow-md animate-pulse border-2 border-red-500">
                                        ⏹️ Zatrzymaj nagrywanie
                                    </button>

                                    <span v-if="isRecording" class="text-red-500 font-bold animate-pulse flex items-center gap-2">
                                        <span class="w-2 h-2 bg-red-500 rounded-full"></span> Nagrywanie trwa...
                                    </span>
                                </div>

                                <div v-if="audioUrl" class="flex items-center gap-4 bg-white p-3 rounded-xl border border-blue-200 shadow-sm w-full md:w-fit">
                                    <audio :src="audioUrl" controls class="h-10 w-full md:w-auto"></audio>
                                    <button @click.prevent="clearRecording" class="text-gray-400 hover:text-red-500 w-10 h-10 flex items-center justify-center rounded-full hover:bg-red-50 transition-colors text-xl shrink-0" title="Usuń to nagranie">
                                        🗑️
                                    </button>
                                </div>

                                <div class="flex items-center gap-4 my-2 opacity-50">
                                    <hr class="flex-1 border-blue-300">
                                    <span class="text-blue-500 text-xs font-bold uppercase tracking-widest">ALBO WGRAJ PLIK</span>
                                    <hr class="flex-1 border-blue-300">
                                </div>

                                <input type="file" @input="clearRecording(); form.audio = $event.target.files[0]" accept="audio/*" class="block w-full text-blue-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white file:text-blue-700 hover:file:bg-blue-100 transition-colors" />
                            </div>

                            <div v-if="form.errors.audio" class="text-red-500 text-sm mt-2 font-medium">{{ form.errors.audio }}</div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                            <Link :href="route('dashboard')" class="px-6 py-4 bg-gray-100 font-bold rounded-2xl text-gray-700 hover:bg-gray-200 transition-colors">
                                Anuluj
                            </Link>
                            <button type="submit" :disabled="isFakeLoading || form.processing" class="px-6 py-4 bg-blue-600 font-bold rounded-2xl text-white hover:bg-blue-700 disabled:opacity-50 shadow-md shadow-blue-200 transition-all active:scale-95">
                                💾 Zapisz piktogram
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div v-if="isFakeLoading || form.processing" class="fixed inset-0 z-[200] flex flex-col items-center justify-center bg-white/90 backdrop-blur-md transition-all duration-300">
            <div class="text-7xl animate-bounce mb-6">🚀</div>
            <h2 class="text-3xl font-black text-blue-600 mb-2">Tworzenie magii...</h2>
            <p class="text-gray-500 font-bold text-lg">Zapisuję Twój piktogram w bazie danych!</p>
        </div>

    </AuthenticatedLayout>
</template>
