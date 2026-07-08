<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useTTS } from '@/Composables/useTTS';
import Icon from '@/Components/Icon.vue';
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

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

const ACCENT_PRESETS = ['#facc15', '#ef4444', '#22d3ee', '#a3e635', '#f472b6', '#ffffff'];

const form = useForm({
    name: '',
    avatar: null,
    is_cvi_mode: false,
    cvi_accent_color: '#facc15',
    cvi_grid_density: 'normal',
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
    form.cvi_accent_color = child.cvi_accent_color || '#facc15';
    form.cvi_grid_density = child.cvi_grid_density || 'normal';
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

const actions = [
    { name: 'board', label: 'Tablica', icon: 'layout', classes: 'bg-primary-500 hover:bg-primary-600' },
    { name: 'manage', label: 'Słowa', icon: 'book', classes: 'bg-purple-500 hover:bg-purple-600' },
    { name: 'schedule-board', label: 'Plan', icon: 'arrow-right', classes: 'bg-green-500 hover:bg-green-600' },
    { name: 'manage-schedule', label: 'Ułóż', icon: 'calendar', classes: 'bg-orange-500 hover:bg-orange-600' },
    { name: 'quiz', label: 'Quiz', icon: 'dice', classes: 'bg-teal-500 hover:bg-teal-600' },
];
</script>

<template>
    <Head title="Profile Dzieci" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Moje Dzieci</h2>
                <div class="flex items-center gap-3">
                    <Link :href="route('templates.index')" class="inline-flex items-center gap-2 bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                        <Icon name="grid" :size="18" /> <span class="hidden sm:inline">Szablony</span>
                    </Link>
                    <Link :href="route('children.create')" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                        <Icon name="plus" :size="18" /> Dodaj profil
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-surface-dark-muted overflow-hidden shadow-sm sm:rounded-card p-6 border border-gray-100 dark:border-gray-700">

                    <div v-if="children.length === 0" class="text-center py-16 text-gray-500 dark:text-gray-400">
                        <div class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                            <Icon name="user" :size="32" />
                        </div>
                        <p class="font-medium">Nie masz jeszcze dodanych profili. Kliknij „Dodaj profil", aby zacząć.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="child in children" :key="child.id" class="border-2 border-gray-200 dark:border-gray-700 rounded-card p-6 flex flex-col items-center hover:border-primary-400 dark:hover:border-primary-500 transition shadow-sm relative bg-white dark:bg-surface-dark-muted">

                            <button @click="openSettingsModal(child)" aria-label="Ustawienia" class="absolute top-4 right-4 text-gray-400 hover:text-primary-600 transition">
                                <Icon name="settings" :size="22" />
                            </button>

                            <div class="w-24 h-24 rounded-full overflow-hidden bg-primary-100 dark:bg-primary-900/40 border-4 border-white dark:border-gray-700 shadow-inner mb-4 flex items-center justify-center text-primary-400">
                                <img v-if="child.avatar_path" :src="child.avatar_path" class="w-full h-full object-cover" />
                                <Icon v-else name="user" :size="40" />
                            </div>

                            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">{{ child.name }}</h3>

                            <div v-if="child.is_cvi_mode" class="mb-4 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200 px-3 py-1 rounded-full text-xs font-bold tracking-wider">
                                TRYB CVI
                            </div>
                            <div v-else class="mb-4 h-6"></div>

                            <div class="grid grid-cols-2 gap-3 w-full mt-2">
                                <Link v-for="a in actions" :key="a.name" :href="route('children.' + a.name, child.id)"
                                      class="inline-flex items-center justify-center gap-2 text-white text-center py-3 rounded-xl transition font-bold shadow-sm active:scale-95"
                                      :class="a.classes">
                                    <Icon :name="a.icon" :size="18" /> {{ a.label }}
                                </Link>
                                <Link :href="route('children.export-pdf', child.id)" target="_blank"
                                      class="inline-flex items-center justify-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-center py-3 rounded-xl transition font-bold shadow-sm active:scale-95">
                                    <Icon name="printer" :size="18" /> PDF
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal ustawień -->
        <BaseModal :show="showSettingsModal" max-width="md" @close="closeSettingsModal">
            <div class="p-8 max-h-[85vh] flex flex-col">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 shrink-0 flex items-center gap-2">
                    <Icon name="settings" :size="24" /> Ustawienia: {{ selectedChild?.name }}
                </h3>
                <form @submit.prevent="submitUpdate" class="flex flex-col gap-6 overflow-y-auto pr-2">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-200 mb-2">Imię dziecka</label>
                        <input type="text" v-model="form.name" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" required />
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-200 mb-2">Zdjęcie profilowe</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0 flex items-center justify-center text-gray-400">
                                <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
                                <img v-else-if="selectedChild?.avatar_path" :src="selectedChild.avatar_path" class="w-full h-full object-cover" />
                                <Icon v-else name="user" :size="28" />
                            </div>
                            <input type="file" @change="handleAvatarChange" accept="image/*" class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                        </div>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-200 dark:border-amber-800">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="form.is_cvi_mode" class="w-5 h-5 text-amber-500 border-gray-300 rounded focus:ring-amber-500" />
                            <div>
                                <span class="block font-bold text-amber-900 dark:text-amber-200">Tryb CVI (Wysoki Kontrast)</span>
                                <span class="text-sm text-amber-700 dark:text-amber-300/80">Czarne tło i jaskrawe ramki na tablicy</span>
                            </div>
                        </label>

                        <div v-if="form.is_cvi_mode" class="mt-4 pt-4 border-t border-amber-200 dark:border-amber-800 space-y-4 animate-fade-in">
                            <div>
                                <span class="block text-sm font-bold text-amber-800 dark:text-amber-200 mb-2">Kolor akcentu</span>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="c in ACCENT_PRESETS" :key="c" type="button" @click="form.cvi_accent_color = c"
                                            :aria-label="'Kolor ' + c"
                                            class="w-9 h-9 rounded-full border-2 transition active:scale-90"
                                            :class="form.cvi_accent_color === c ? 'ring-4 ring-offset-2 ring-amber-400 border-white' : 'border-gray-300'"
                                            :style="{ backgroundColor: c }"></button>
                                    <input type="color" v-model="form.cvi_accent_color" aria-label="Własny kolor akcentu" class="w-9 h-9 rounded-full border-0 bg-transparent cursor-pointer p-0" />
                                </div>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-amber-800 dark:text-amber-200 mb-2">Gęstość siatki (liczba bodźców)</span>
                                <div class="grid grid-cols-3 gap-2">
                                    <button v-for="d in [
                                        { v: 'compact', l: 'Gęsta' },
                                        { v: 'normal', l: 'Zwykła' },
                                        { v: 'spacious', l: 'Rzadka' },
                                    ]" :key="d.v" type="button" @click="form.cvi_grid_density = d.v"
                                        class="py-2 rounded-lg text-sm font-bold transition active:scale-95"
                                        :class="form.cvi_grid_density === d.v ? 'bg-amber-500 text-white shadow' : 'bg-white dark:bg-gray-700 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800'">
                                        {{ d.l }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-primary-50 dark:bg-primary-900/20 p-6 rounded-2xl border border-primary-100 dark:border-primary-800">
                        <label class="font-bold text-primary-900 dark:text-primary-200 text-lg mb-4 flex items-center gap-2"><Icon name="volume" :size="20" /> Głos (TTS)</label>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-primary-800 dark:text-primary-200 mb-1">Wybierz głos</label>
                                <select v-model="form.tts_voice" class="block w-full rounded-xl border-primary-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 py-2 px-3 text-sm">
                                    <option value="">Domyślny systemowy</option>
                                    <option v-for="voice in voices" :key="voice.voiceURI" :value="voice.name">
                                        {{ voice.friendlyName }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs font-bold text-primary-800 dark:text-primary-200 mb-1 flex justify-between"><span>Prędkość</span><span>{{ form.tts_rate }}</span></label>
                                    <input type="range" v-model="form.tts_rate" min="0.1" max="2" step="0.1" class="w-full h-2 bg-primary-200 rounded-lg appearance-none cursor-pointer" />
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-primary-800 dark:text-primary-200 mb-1 flex justify-between"><span>Ton</span><span>{{ form.tts_pitch }}</span></label>
                                    <input type="range" v-model="form.tts_pitch" min="0" max="2" step="0.1" class="w-full h-2 bg-primary-200 rounded-lg appearance-none cursor-pointer" />
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-primary-800 dark:text-primary-200 mb-1 flex justify-between"><span>Głośność</span><span>{{ form.tts_volume }}</span></label>
                                    <input type="range" v-model="form.tts_volume" min="0" max="1" step="0.1" class="w-full h-2 bg-primary-200 rounded-lg appearance-none cursor-pointer" />
                                </div>
                            </div>
                            <button type="button" @click="testVoice" class="w-full mt-4 inline-flex items-center justify-center gap-2 bg-primary-200 hover:bg-primary-300 text-primary-800 font-bold py-2 px-4 rounded-xl transition">
                                <Icon name="volume" :size="18" /> Testuj głos
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-between items-center mt-4 pt-6 border-t border-gray-100 dark:border-gray-700 shrink-0 gap-4">
                        <button type="button" @click="openDeleteModal" class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 dark:bg-red-900/30 font-bold rounded-md text-red-700 dark:text-red-300 hover:bg-red-200">
                            <Icon name="trash" :size="18" /> Usuń profil
                        </button>
                        <div class="flex gap-3">
                            <button type="button" @click="closeSettingsModal" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 font-bold rounded-md text-gray-800 dark:text-gray-100 hover:bg-gray-300">Anuluj</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 font-bold rounded-md text-white hover:bg-primary-700 disabled:opacity-50">Zapisz</button>
                        </div>
                    </div>
                </form>
            </div>
        </BaseModal>

        <ConfirmDialog
            :show="showDeleteModal"
            title="Usuń profil dziecka"
            :message="`Czy na pewno chcesz usunąć profil ${selectedChild?.name}? Te dane przepadną bezpowrotnie.`"
            confirm-label="Usuń"
            cancel-label="Anuluj"
            icon="trash"
            variant="danger"
            @confirm="confirmDelete"
            @cancel="closeDeleteModal"
        />
    </AuthenticatedLayout>
</template>
