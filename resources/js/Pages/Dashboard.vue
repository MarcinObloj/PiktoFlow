<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/Components/Icon.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

defineProps({
    myCategories: Array,
    myPictograms: Array
});

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

const tiles = [
    { route: 'categories.create', icon: 'folder', color: 'purple', title: 'Kategorie', desc: 'Zdefiniuj grupy słów, np. "Jedzenie".', cta: '+ Nowa kategoria' },
    { route: 'pictograms.create', icon: 'image', color: 'green', title: 'Wgraj własne', desc: 'Dodaj własne zdjęcia z dysku.', cta: '+ Dodaj plik' },
    { route: 'pictograms.arasaac', icon: 'search', color: 'primary', title: 'Baza ARASAAC', desc: 'Wyszukaj gotowe piktogramy z sieci.', cta: 'Znajdź w sieci' },
    { route: 'templates.index', icon: 'grid', color: 'amber', title: 'Szablony', desc: 'Przypisz gotowe zestawy słów.', cta: 'Przeglądaj' },
    { route: 'statistics.index', icon: 'chart', color: 'rose', title: 'Analityka', desc: 'Śledź postępy i rozwój dzieci.', cta: 'Zobacz statystyki' },
];

const colorMap = {
    purple: { chip: 'bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300', cta: 'bg-purple-50 text-purple-700 hover:bg-purple-100 border-purple-100 dark:bg-purple-900/30 dark:text-purple-200 dark:border-purple-800' },
    green: { chip: 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300', cta: 'bg-green-50 text-green-700 hover:bg-green-100 border-green-100 dark:bg-green-900/30 dark:text-green-200 dark:border-green-800' },
    primary: { chip: 'bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300', cta: 'bg-primary-50 text-primary-700 hover:bg-primary-100 border-primary-100 dark:bg-primary-900/30 dark:text-primary-200 dark:border-primary-800' },
    amber: { chip: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300', cta: 'bg-amber-50 text-amber-700 hover:bg-amber-100 border-amber-100 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800' },
    rose: { chip: 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300', cta: 'bg-rose-50 text-rose-700 hover:bg-rose-100 border-rose-100 dark:bg-rose-900/30 dark:text-rose-200 dark:border-rose-800' },
};
</script>

<template>
    <Head title="Główna Tablica" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 dark:text-gray-100 leading-tight">Główna Tablica Opiekuna</h2>
        </template>

        <div class="py-8 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-12">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">

                    <div class="md:col-span-full bg-gradient-to-br from-white to-primary-50 dark:from-surface-dark-muted dark:to-primary-900/20 overflow-hidden shadow-sm rounded-card border border-primary-100 dark:border-gray-700 p-6 sm:p-10 flex flex-col md:flex-row justify-between items-center gap-6 relative">
                        <div class="absolute -top-6 -right-6 sm:-top-8 sm:-right-8 opacity-10 pointer-events-none text-primary-600">
                            <Icon name="puzzle" :size="140" />
                        </div>
                        <div class="text-center md:text-left relative z-10">
                            <h3 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white mb-3 tracking-tight">Witaj w PiktoFlow!</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-lg sm:text-xl max-w-xl leading-relaxed">Zarządzaj profilami dzieci, twórz spersonalizowane tablice i buduj komunikację bez barier.</p>
                        </div>
                        <Link :href="route('children.index')" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold py-4 px-8 rounded-2xl shadow-xl shadow-primary-200 dark:shadow-none transition-all active:scale-95 whitespace-nowrap w-full md:w-auto text-center text-lg z-10 border border-primary-500">
                            Profile dzieci <Icon name="arrow-right" :size="20" />
                        </Link>
                    </div>

                    <div v-for="tile in tiles" :key="tile.route" class="bg-white dark:bg-surface-dark-muted overflow-hidden shadow-sm hover:shadow-md transition-shadow rounded-card border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between group">
                        <div>
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform" :class="colorMap[tile.color].chip">
                                <Icon :name="tile.icon" :size="24" />
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ tile.title }}</h4>
                            <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed text-sm">{{ tile.desc }}</p>
                        </div>
                        <Link :href="route(tile.route)" class="font-bold py-3 px-4 rounded-xl text-center transition-colors border" :class="colorMap[tile.color].cta">
                            {{ tile.cta }}
                        </Link>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-surface-dark-muted rounded-card p-6 sm:p-10 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <Icon name="book" :size="28" class="text-gray-700 dark:text-gray-200" />
                        <h3 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">Twoja Biblioteka AAC</h3>
                    </div>

                    <div class="mb-12">
                        <h4 class="text-sm font-black text-gray-500 dark:text-gray-400 mb-6 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-8 h-[2px] bg-gray-300 dark:bg-gray-600 rounded"></span> Twoje Kategorie
                        </h4>
                        <div v-if="myCategories.length === 0" class="text-gray-500 dark:text-gray-400 font-medium italic bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 text-center shadow-sm">
                            Brak własnych kategorii.
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <div v-for="cat in myCategories" :key="cat.id" class="bg-white dark:bg-gray-800 border-l-8 rounded-2xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition-shadow group" :style="{ borderLeftColor: cat.color }">
                                <span class="font-bold text-gray-800 dark:text-gray-100 text-lg truncate pr-4 pl-2">{{ cat.name }}</span>
                                <button @click="openDeleteModal('category', cat.id)" aria-label="Usuń kategorię" class="text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 w-10 h-10 rounded-xl flex items-center justify-center transition-colors shrink-0">
                                    <Icon name="trash" :size="20" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-black text-gray-500 dark:text-gray-400 mb-6 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-8 h-[2px] bg-gray-300 dark:bg-gray-600 rounded"></span> Twoje Piktogramy
                        </h4>
                        <div v-if="myPictograms.length === 0" class="text-gray-500 dark:text-gray-400 font-medium italic bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 text-center shadow-sm">
                            Brak wgranych obrazków.
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
                            <div v-for="pic in myPictograms" :key="pic.id" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 flex flex-col items-center shadow-sm hover:shadow-md transition-shadow relative group">
                                <button @click="openDeleteModal('pictogram', pic.id)" aria-label="Usuń piktogram" class="absolute -top-3 -right-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm text-gray-400 hover:text-white hover:bg-red-500 hover:border-red-500 hover:shadow-md w-9 h-9 rounded-full flex items-center justify-center opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-all z-10">
                                    <Icon name="trash" :size="16" />
                                </button>
                                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-50 dark:bg-gray-700 rounded-xl flex items-center justify-center mb-3 overflow-hidden p-2">
                                    <img :src="pic.image_path" :alt="pic.name" class="w-full h-full object-contain" />
                                </div>
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100 text-center truncate w-full" :title="pic.name">{{ pic.name }}</span>
                                <span v-if="pic.category" class="text-[10px] font-bold uppercase tracking-wider mt-1 truncate w-full text-center" :style="{ color: pic.category.color }">{{ pic.category.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :show="showDeleteModal"
            :title="deleteType === 'category' ? 'Usuń kategorię' : 'Usuń piktogram'"
            :message="deleteType === 'category' ? 'UWAGA: Wszystkie piktogramy w tej kategorii również znikną!' : 'Piktogram zostanie trwale usunięty.'"
            confirm-label="Usuń"
            cancel-label="Anuluj"
            icon="trash"
            variant="danger"
            @confirm="confirmDeletion"
            @cancel="closeDeleteModal"
        />
    </AuthenticatedLayout>
</template>
