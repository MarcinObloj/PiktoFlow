<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import Icon from '@/Components/Icon.vue';
import Spinner from '@/Components/Spinner.vue';
import BaseModal from '@/Components/BaseModal.vue';

const props = defineProps({
    categories: Array
});

const searchQuery = ref('');
const results = ref([]);
const loading = ref(false);
const hasSearched = ref(false);
const selectedCategoryId = ref('');
const showCategoryAlert = ref(false);
const isSaving = ref(false);

const search = async () => {
    if (searchQuery.value.length < 2) return;
    loading.value = true;
    hasSearched.value = true;
    try {
        const response = await axios.get(route('arasaac.search'), { params: { q: searchQuery.value } });
        results.value = Array.isArray(response.data) ? response.data : [];
    } catch (e) {
        console.error("Błąd wyszukiwania");
        results.value = [];
    } finally {
        loading.value = false;
    }
};

const addPictogram = (id, name) => {
    if (!selectedCategoryId.value) {
        showCategoryAlert.value = true;
        return;
    }

    isSaving.value = true;
    router.post(route('pictograms.store'), {
        name: name,
        category_id: selectedCategoryId.value,
        image_url: `https://static.arasaac.org/pictograms/${id}/${id}_300.png`
    }, {
        onFinish: () => {
            isSaving.value = false;
        }
    });
};
</script>

<template>
    <Head title="Szukaj w ARASAAC" />
    <AuthenticatedLayout>
        <div class="py-12 px-4 max-w-5xl mx-auto relative">
            <div class="bg-white dark:bg-surface-dark-muted p-8 rounded-card shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-3xl font-black mb-6 flex items-center gap-3 text-gray-900 dark:text-white">
                    <Icon name="search" :size="28" class="text-primary-600 dark:text-primary-400" /> Znajdź Piktogram ARASAAC
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <input v-model="searchQuery" @keyup.enter="search" placeholder="Wpisz np. rower, pies, dom..." class="rounded-2xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white p-4 focus:ring-primary-500 focus:border-primary-500" />
                    <select v-model="selectedCategoryId" class="rounded-2xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white p-4">
                        <option value="">-- Wybierz kategorię docelową --</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <button @click="search" :disabled="loading" class="w-full inline-flex items-center justify-center gap-2 bg-primary-600 text-white font-bold py-4 rounded-2xl hover:bg-primary-700 transition mb-10 disabled:opacity-60">
                    <Spinner v-if="loading" :size="20" />
                    <Icon v-else name="search" :size="20" />
                    {{ loading ? 'Szukanie...' : 'Szukaj w bazie' }}
                </button>

                <!-- Skeleton podczas ładowania -->
                <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    <div v-for="n in 8" :key="'sk-' + n" class="border border-gray-100 dark:border-gray-700 p-4 rounded-2xl flex flex-col items-center">
                        <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse mb-3"></div>
                        <div class="h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-3"></div>
                        <div class="h-8 w-full bg-gray-100 dark:bg-gray-800 rounded-xl animate-pulse"></div>
                    </div>
                </div>

                <!-- Wyniki -->
                <div v-else-if="results.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    <div v-for="pic in results" :key="pic._id" class="border border-gray-100 dark:border-gray-700 p-4 rounded-2xl flex flex-col items-center hover:bg-primary-50 dark:hover:bg-gray-700 transition group relative shadow-sm hover:shadow-md">
                        <img :src="`https://static.arasaac.org/pictograms/${pic._id}/${pic._id}_300.png`" class="w-24 h-24 object-contain mb-3" loading="lazy" />
                        <span class="font-bold text-sm text-center text-gray-800 dark:text-gray-100">{{ searchQuery }}</span>
                        <button @click="addPictogram(pic._id, searchQuery)" class="mt-3 inline-flex items-center justify-center gap-1 bg-green-500 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-green-600 active:scale-95 transition-transform w-full">
                            <Icon name="plus" :size="14" /> Dodaj
                        </button>
                    </div>
                </div>

                <!-- Pusty stan -->
                <div v-else-if="hasSearched" class="text-center py-16 text-gray-400 dark:text-gray-500">
                    <div class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <Icon name="search" :size="32" />
                    </div>
                    <p class="font-medium">Brak wyników dla „{{ searchQuery }}". Spróbuj innego słowa.</p>
                </div>

                <div v-else class="text-center py-16 text-gray-400 dark:text-gray-500">
                    <div class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <Icon name="inbox" :size="32" />
                    </div>
                    <p class="font-medium">Wpisz słowo powyżej, aby przeszukać bazę ponad 10 000 piktogramów.</p>
                </div>
            </div>
        </div>

        <BaseModal :show="showCategoryAlert" max-width="sm" @close="showCategoryAlert = false">
            <div class="p-8 text-center">
                <div class="mx-auto mb-6 w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300 flex items-center justify-center">
                    <Icon name="alert" :size="32" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Brak kategorii</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-8 font-medium leading-relaxed">
                    Zanim dodasz obrazek do swojej bazy, wybierz najpierw kategorię z rozwijanej listy na górze.
                </p>
                <button @click="showCategoryAlert = false" class="w-full py-4 bg-primary-600 text-white font-bold rounded-2xl hover:bg-primary-700 transition shadow-md active:scale-95">
                    OK, rozumiem
                </button>
            </div>
        </BaseModal>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
        >
            <div v-if="isSaving" class="fixed inset-0 z-[200] flex flex-col items-center justify-center bg-white/90 dark:bg-surface-dark/90 backdrop-blur-md">
                <div class="text-primary-600 dark:text-primary-400 mb-6">
                    <Spinner :size="64" />
                </div>
                <h2 class="text-3xl font-black text-primary-600 dark:text-primary-400 mb-2">Pobieranie obrazka...</h2>
                <p class="text-gray-500 dark:text-gray-400 font-bold text-lg">Dodaję piktogram z bazy ARASAAC do Twojego konta!</p>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
