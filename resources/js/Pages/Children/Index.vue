<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useTTS } from '@/Composables/useTTS';

defineProps({
    children: Array,
});

const showSettingsModal = ref(false);
const showDeleteModal = ref(false);
const selectedChild = ref(null);
const avatarPreview = ref(null);

const { voices, selectedVoice, loadVoices, speak } = useTTS();

onMounted(() => {
    loadVoices();
});

const form = useForm({
    name: '',
    avatar: null,
    is_cvi_mode: false,
    tts_voice: '',
    tts_rate: 0.9,
    tts_pitch: 1.0,
    tts_volume: 1.0,
});

const testVoice = () => {
    let voiceToUse = null;
    if (form.tts_voice) {
        voiceToUse = voices.value.find(v => v.name === form.tts_voice);
    }
    speak('Cześć! Tak brzmi mój nowy głos.', {
        voice: voiceToUse || selectedVoice.value,
        rate: parseFloat(form.tts_rate),
        pitch: parseFloat(form.tts_pitch),
        volume: parseFloat(form.tts_volume)
    });
};

const openSettingsModal = (child) => {
    selectedChild.value = child;
    form.name = child.name;
    form.is_cvi_mode = child.is_cvi_mode;
    form.avatar = null;
    avatarPreview.value = null;
    form.tts_voice = child.tts_voice || '';
    form.tts_rate = child.tts_rate || 0.9;
    form.tts_pitch = child.tts_pitch || 1.0;
    form.tts_volume = child.tts_volume || 1.0;
    showSettingsModal.value = true;
};

const closeSettingsModal = () => {
    showSettingsModal.value = false;
    selectedChild.value = null;
    form.reset();
};

const openDeleteModal = () => {
    showSettingsModal.value = false;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    showSettingsModal.value = true;
};

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const confirmDelete = () => {
    router.delete(route('children.destroy', selectedChild.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedChild.value = null;
        },
    });
};

const submitUpdate = () => {
    form.post(route('children.update', selectedChild.value.id), {
        onSuccess: () => closeSettingsModal(),
    });
};
</script>

<template>
    <Head title="Profile Dzieci" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Moje Dzieci</h2>
                <div class="flex items-center gap-3">
                    <Link :href="route('templates.index')" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                        📦 Szablony
                    </Link>
                    <Link :href="route('children.create')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                        + Dodaj profil
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div v-if="children.length === 0" class="text-center py-8 text-gray-500">
                        Nie masz jeszcze dodanych żadnych profilów. Kliknij przycisk wyżej, aby dodać pierwsze dziecko.
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="child in children" :key="child.id" class="border-2 border-gray-200 rounded-xl p-6 flex flex-col items-center hover:border-blue-400 transition shadow-sm relative">

                            <button @click="openSettingsModal(child)" class="absolute top-4 right-4 text-gray-400 hover:text-blue-600 transition text-xl">
                                ⚙️
                            </button>

                            <div class="w-24 h-24 rounded-full overflow-hidden bg-blue-100 border-4 border-white shadow-inner mb-4 flex items-center justify-center text-4xl">
                                <img v-if="child.avatar_path" :src="child.avatar_path" class="w-full h-full object-cover" />
                                <span v-else>👦</span>
                            </div>

                            <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ child.name }}</h3>

                            <div v-if="child.is_cvi_mode" class="mb-4 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold tracking-wider">
                                TRYB CVI
                            </div>
                            <div v-else class="mb-4 h-6"></div>

                            <div class="grid grid-cols-2 gap-3 w-full mt-2">
                                <Link :href="route('children.board', child.id)" class="bg-blue-500 text-white text-center py-3 rounded-xl hover:bg-blue-600 transition font-bold shadow-sm">
                                    Tablica
                                </Link>
                                <Link :href="route('children.manage', child.id)" class="bg-purple-500 text-white text-center py-3 rounded-xl hover:bg-purple-600 transition font-bold shadow-sm">
                                    Słowa
                                </Link>
                                <Link :href="route('children.schedule-board', child.id)" class="bg-green-500 text-white text-center py-3 rounded-xl hover:bg-green-600 transition font-bold shadow-sm">
                                    ➡️ Plan
                                </Link>
                                <Link :href="route('children.manage-schedule', child.id)" class="bg-orange-500 text-white text-center py-3 rounded-xl hover:bg-orange-600 transition font-bold shadow-sm">
                                    🗓️ Ułóż
                                </Link>
                                <Link :href="route('children.quiz', child.id)" class="bg-teal-500 text-white text-center py-3 rounded-xl hover:bg-teal-600 transition font-bold shadow-sm">
                                    🎲 Quiz
                                </Link>
                                <Link
                                    :href="route('children.export-pdf', child.id)"
                                    target="_blank"
                                    class="bg-gray-700 text-white text-center py-3 rounded-xl hover:bg-gray-800 transition font-bold shadow-sm"
                                >
                                    🖨️ PDF
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal ustawień -->
        <div v-if="showSettingsModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto" @click.self="closeSettingsModal">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl my-8 max-h-[90vh] flex flex-col">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 shrink-0">⚙️ Ustawienia: {{ selectedChild?.name }}</h3>
                <form @submit.prevent="submitUpdate" class="flex flex-col gap-6 overflow-y-auto pr-2">
                    <div>
                        <label class="block font-bold text-gray-700 mb-2">Imię dziecka</label>
                        <input type="text" v-model="form.name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 text-gray-900" required />
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-2">Zdjęcie profilowe</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 shrink-0">
                                <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
                                <img v-else-if="selectedChild?.avatar_path" :src="selectedChild.avatar_path" class="w-full h-full object-cover" />
                                <span v-else class="w-full h-full flex items-center justify-center text-2xl">👦</span>
                            </div>
                            <input type="file" @change="handleAvatarChange" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        </div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-200">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="form.is_cvi_mode" class="w-5 h-5 text-yellow-500 border-gray-300 rounded focus:ring-yellow-500" />
                            <div>
                                <span class="block font-bold text-yellow-900">Tryb CVI (Wysoki Kontrast)</span>
                                <span class="text-sm text-yellow-700">Czarne tło i jaskrawe ramki na tablicy</span>
                            </div>
                        </label>
                    </div>
                    <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                        <label class="block font-bold text-blue-900 text-lg mb-4">🗣️ Głos (TTS)</label>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-blue-800 mb-1">Wybierz głos</label>
                                <select v-model="form.tts_voice" class="block w-full rounded-xl border-blue-300 shadow-sm focus:border-blue-500 py-2 px-3 text-sm">
                                    <option value="">Domyślny systemowy</option>
                                    <option v-for="voice in voices" :key="voice.voiceURI" :value="voice.name">
                                        {{ voice.friendlyName }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-blue-800 mb-1 flex justify-between"><span>Prędkość</span><span>{{ form.tts_rate }}</span></label>
                                    <input type="range" v-model="form.tts_rate" min="0.1" max="2" step="0.1" class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-blue-800 mb-1 flex justify-between"><span>Ton</span><span>{{ form.tts_pitch }}</span></label>
                                    <input type="range" v-model="form.tts_pitch" min="0" max="2" step="0.1" class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-blue-800 mb-1 flex justify-between"><span>Głośność</span><span>{{ form.tts_volume }}</span></label>
                                    <input type="range" v-model="form.tts_volume" min="0" max="1" step="0.1" class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer" />
                                </div>
                            </div>
                            <button type="button" @click="testVoice" class="w-full mt-4 bg-blue-200 hover:bg-blue-300 text-blue-800 font-bold py-2 px-4 rounded-xl transition flex items-center justify-center gap-2">
                                <span>🔊</span> Testuj głos
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-between items-center mt-4 pt-6 border-t border-gray-100 shrink-0 gap-4">
                        <button type="button" @click="openDeleteModal" class="px-4 py-2 bg-red-100 font-bold rounded-md text-red-700 hover:bg-red-200">
                            🗑️ Usuń profil
                        </button>
                        <div class="flex gap-3">
                            <button type="button" @click="closeSettingsModal" class="px-4 py-2 bg-gray-200 font-bold rounded-md text-gray-800 hover:bg-gray-300">Anuluj</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 font-bold rounded-md text-white hover:bg-blue-700 disabled:opacity-50">Zapisz</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 z-[110] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-[2rem] p-8 sm:p-10 max-w-md w-full shadow-2xl text-center border-4 border-red-100 flex flex-col items-center">
                <div class="text-6xl mb-6">🗑️</div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">Usuń profil dziecka</h3>
                <p class="text-gray-600 mb-8 font-medium leading-relaxed italic">
                    Czy na pewno chcesz usunąć profil <strong>{{ selectedChild?.name }}</strong>? Te dane przepadną bezpowrotnie.
                </p>
                <div class="grid grid-cols-2 gap-4 w-full mt-2">
                    <button @click="closeDeleteModal" class="py-4 bg-gray-200 text-gray-800 font-bold rounded-xl hover:bg-gray-300 transition">Anuluj</button>
                    <button @click="confirmDelete" class="py-4 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition shadow-md">Usuń</button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
