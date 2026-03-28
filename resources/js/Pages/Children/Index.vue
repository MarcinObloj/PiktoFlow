<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    children: Array,
});

const showSettingsModal = ref(false);
const selectedChild = ref(null);
const avatarPreview = ref(null);

const form = useForm({
    name: '',
    avatar: null,
    is_cvi_mode: false,
});

const openSettingsModal = (child) => {
    selectedChild.value = child;
    form.name = child.name;
    form.is_cvi_mode = child.is_cvi_mode;
    form.avatar = null;
    avatarPreview.value = null;
    showSettingsModal.value = true;
};

const closeSettingsModal = () => {
    showSettingsModal.value = false;
    selectedChild.value = null;
    form.reset();
};

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
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
                <Link :href="route('children.create')" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow transition">
                    + Dodaj profil
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div v-if="children.length === 0" class="text-center py-8 text-gray-500">
                        Nie masz jeszcze dodanych żadnych profili. Kliknij przycisk wyżej, aby dodać pierwsze dziecko.
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

                            <div class="flex gap-2 w-full">
                                <Link :href="route('children.board', child.id)" class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 transition font-semibold">
                                    Tablica
                                </Link>
                                <Link :href="route('children.manage', child.id)" class="flex-1 bg-purple-500 text-white text-center py-2 rounded hover:bg-purple-600 transition font-semibold">
                                    Słowa
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div v-if="showSettingsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" @click.self="closeSettingsModal">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">⚙️ Ustawienia: {{ selectedChild?.name }}</h3>

                <form @submit.prevent="submitUpdate" class="flex flex-col gap-6">

                    <div>
                        <label class="block font-bold text-gray-700 mb-2">Imię dziecka</label>
                        <input type="text" v-model="form.name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3" required />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-2">Zdjęcie profilowe</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 shrink-0">
                                <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
                                <img v-else-if="selectedChild?.avatar_path" :src="selectedChild.avatar_path" class="w-full h-full object-cover" />
                                <span v-else class="w-full h-full flex items-center justify-center text-2xl">👦</span>
                            </div>
                            <input type="file" @change="handleAvatarChange" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
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

                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" @click="closeSettingsModal" class="px-4 py-2 bg-gray-100 font-bold rounded-md text-gray-700 hover:bg-gray-200">
                            Anuluj
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 font-bold rounded-md text-white hover:bg-blue-700 disabled:opacity-50">
                            Zapisz
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
