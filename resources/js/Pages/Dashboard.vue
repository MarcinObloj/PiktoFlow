<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    myCategories: Array,
    myPictograms: Array
});

// --- SYSTEM MODALA USUWANIA ---
const showDeleteModal = ref(false);
const deleteType = ref('');
const deleteId = ref(null);

const openDeleteModal = (type, id) => {
    deleteType.value = type;
    deleteId.value = id;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteType.value = '';
    deleteId.value = null;
};

const confirmDeletion = () => {
    if (deleteType.value === 'category') {
        router.delete(route('categories.destroy', deleteId.value), {
            onSuccess: () => closeDeleteModal()
        });
    } else if (deleteType.value === 'pictogram') {
        router.delete(route('pictograms.destroy', deleteId.value), {
            onSuccess: () => closeDeleteModal()
        });
    }
};
</script>

<template>
    <Head title="Główna Tablica" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">Główna Tablica Opiekuna</h2>
        </template>

        <div class="py-8 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-12">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="md:col-span-3 bg-gradient-to-br from-white to-blue-50 overflow-hidden shadow-sm sm:rounded-3xl border border-blue-100 p-6 sm:p-10 flex flex-col md:flex-row justify-between items-center gap-6 relative rounded-2xl">
                        <div class="absolute -top-6 -right-6 sm:-top-10 sm:-right-10 text-8xl sm:text-9xl opacity-10 pointer-events-none">🧩</div>
                        <div class="text-center md:text-left relative z-10">
                            <h3 class="text-3xl sm:text-4xl font-black text-gray-900 mb-3 tracking-tight">Witaj w PiktoFlow!</h3>
                            <p class="text-gray-600 text-lg sm:text-xl max-w-xl leading-relaxed">Zarządzaj profilami dzieci, twórz spersonalizowane tablice i buduj komunikację bez barier.</p>
                        </div>
                        <Link :href="route('children.index')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-xl shadow-blue-200 transition-all active:scale-95 whitespace-nowrap w-full md:w-auto text-center text-lg z-10 border border-blue-500">
                            Profile dzieci →
                        </Link>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow rounded-2xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8 flex flex-col justify-between group">
                        <div>
                            <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform">📁</div>
                            <h4 class="text-2xl font-bold text-gray-900 mb-3">Kategorie</h4>
                            <p class="text-gray-500 mb-8 leading-relaxed text-sm">Zdefiniuj grupy słów, np. "Jedzenie" lub "Emocje".</p>
                        </div>
                        <Link :href="route('categories.create')" class="bg-purple-50 text-purple-700 font-bold py-4 px-6 rounded-2xl hover:bg-purple-100 text-center transition-colors border border-purple-100">
                            + Nowa kategoria
                        </Link>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow rounded-2xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8 flex flex-col justify-between group">
                        <div>
                            <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform">🖼️</div>
                            <h4 class="text-2xl font-bold text-gray-900 mb-3">Wgraj własne</h4>
                            <p class="text-gray-500 mb-8 leading-relaxed text-sm">Dodaj własne zdjęcia prosto z aparatu lub dysku.</p>
                        </div>
                        <Link :href="route('pictograms.create')" class="bg-green-50 text-green-700 font-bold py-4 px-6 rounded-2xl hover:bg-green-100 text-center transition-colors border border-green-100">
                            + Dodaj plik
                        </Link>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow rounded-2xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8 flex flex-col justify-between group">
                        <div>
                            <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform">🔍</div>
                            <h4 class="text-2xl font-bold text-gray-900 mb-3">Baza ARASAAC</h4>
                            <p class="text-gray-500 mb-8 leading-relaxed text-sm">Wyszukaj gotowe, profesjonalne piktogramy z bazy online.</p>
                        </div>
                        <Link :href="route('pictograms.arasaac')" class="bg-blue-50 text-blue-700 font-bold py-4 px-6 rounded-2xl hover:bg-blue-100 text-center transition-colors border border-blue-100">
                            Znajdź w sieci
                        </Link>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl sm:rounded-3xl p-6 sm:p-10 border border-gray-200">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-200">
                        <span class="text-3xl">📚</span>
                        <h3 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Twoja Biblioteka AAC</h3>
                    </div>

                    <div class="mb-12">
                        <h4 class="text-sm font-black text-gray-500 mb-6 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-8 h-[2px] bg-gray-300 rounded"></span> Twoje Kategorie
                        </h4>
                        <div v-if="myCategories.length === 0" class="text-gray-500 font-medium italic bg-white p-6 rounded-2xl border border-gray-100 text-center shadow-sm">
                            Brak własnych kategorii.
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <div v-for="cat in myCategories" :key="cat.id" class="bg-white border-l-8 rounded-2xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition-shadow group" :style="{ borderLeftColor: cat.color }">
                                <span class="font-bold text-gray-800 text-lg truncate pr-4 pl-2">{{ cat.name }}</span>
                                <button @click="openDeleteModal('category', cat.id)" class="text-gray-400 hover:text-red-600 hover:bg-red-50 w-10 h-10 rounded-xl flex items-center justify-center transition-colors shrink-0">
                                    <span class="text-xl">🗑️</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-black text-gray-500 mb-6 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-8 h-[2px] bg-gray-300 rounded"></span> Twoje Piktogramy
                        </h4>
                        <div v-if="myPictograms.length === 0" class="text-gray-500 font-medium italic bg-white p-6 rounded-2xl border border-gray-100 text-center shadow-sm">
                            Brak wgranych obrazków.
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
                            <div v-for="pic in myPictograms" :key="pic.id" class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-col items-center shadow-sm hover:shadow-md transition-shadow relative group">
                                <button @click="openDeleteModal('pictogram', pic.id)" class="absolute -top-3 -right-3 bg-white border border-gray-200 shadow-sm text-gray-400 hover:text-white hover:bg-red-500 hover:border-red-500 hover:shadow-md w-9 h-9 rounded-full flex items-center justify-center opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-all z-10 text-lg">
                                    🗑️
                                </button>
                                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-50 rounded-xl flex items-center justify-center mb-3 overflow-hidden p-2">
                                    <img :src="pic.image_path" :alt="pic.name" class="w-full h-full object-contain" />
                                </div>
                                <span class="text-sm font-bold text-gray-800 text-center truncate w-full" :title="pic.name">{{ pic.name }}</span>
                                <span v-if="pic.category" class="text-[10px] font-bold uppercase tracking-wider mt-1 truncate w-full text-center" :style="{ color: pic.category.color }">{{ pic.category.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 transition-all">
            <div class="bg-white rounded-[2rem] p-8 sm:p-10 max-w-md w-full shadow-2xl text-center transform transition-all border-4 border-red-50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-500 rounded-full -mr-16 -mt-16 opacity-10"></div>
                <div class="text-6xl mb-6">🗑️</div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">
                    {{ deleteType === 'category' ? 'Usuń kategorię' : 'Usuń piktogram' }}
                </h3>
                <p class="text-gray-600 mb-8 font-medium leading-relaxed italic">
                    {{ deleteType === 'category' ? 'UWAGA: Wszystkie piktogramy w tej kategorii również znikną!' : 'Piktogram zostanie trwale usunięty.' }}
                </p>
                <div class="flex flex-col sm:flex-row gap-3 mt-6">
                    <button @click="closeDeleteModal" class="flex-1 px-4 py-4 bg-gray-100 text-gray-700 font-bold rounded-2xl hover:bg-gray-200 transition active:scale-95">Anuluj</button>
                    <button @click="confirmDeletion" class="flex-1 px-4 py-4 bg-red-500 text-white font-bold rounded-2xl hover:bg-red-600 transition shadow-lg shadow-red-200 active:scale-95">Usuń</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
