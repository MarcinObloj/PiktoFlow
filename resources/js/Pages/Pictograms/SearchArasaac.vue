<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    categories: Array
});

const searchQuery = ref('');
const results = ref([]);
const loading = ref(false);
const selectedCategoryId = ref('');
const showCategoryAlert = ref(false);
const isFakeLoading = ref(false);

const search = async () => {
    if (searchQuery.value.length < 2) return;
    loading.value = true;
    try {
        const response = await axios.get(route('arasaac.search'), { params: { q: searchQuery.value } });
        results.value = Array.isArray(response.data) ? response.data : [];
    } catch (e) {
        console.error("Błąd wyszukiwania");
    } finally {
        loading.value = false;
    }
};

const addPictogram = (id, name) => {
    if (!selectedCategoryId.value) {
        showCategoryAlert.value = true;
        return;
    }

    isFakeLoading.value = true;
    router.post(route('pictograms.store'), {
        name: name,
        category_id: selectedCategoryId.value,
        image_url: `https://static.arasaac.org/pictograms/${id}/${id}_300.png`
    }, {
        onFinish: () => {
            isFakeLoading.value = false;
        }
    });
};
</script>

<template>
    <Head title="Szukaj w ARASAAC" />
    <AuthenticatedLayout>
        <div class="py-12 px-4 max-w-5xl mx-auto relative">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h2 class="text-3xl font-black mb-6">🔍 Znajdź Piktogram ARASAAC</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <input v-model="searchQuery" @keyup.enter="search" placeholder="Wpisz np. rower, pies, dom..." class="rounded-2xl border-gray-200 p-4 focus:ring-blue-500" />
                    <select v-model="selectedCategoryId" class="rounded-2xl border-gray-200 p-4">
                        <option value="">-- Wybierz kategorię docelową --</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <button @click="search" class="w-full bg-blue-600 text-white font-bold py-4 rounded-2xl hover:bg-blue-700 transition mb-10">
                    Szukaj w bazie
                </button>

                <div v-if="loading" class="text-center py-10 font-bold text-gray-500">Szukanie...</div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    <div v-for="pic in results" :key="pic._id" class="border border-gray-100 p-4 rounded-2xl flex flex-col items-center hover:bg-blue-50 transition group relative shadow-sm hover:shadow-md">
                        <img :src="`https://static.arasaac.org/pictograms/${pic._id}/${pic._id}_300.png`" class="w-24 h-24 object-contain mb-3" />
                        <span class="font-bold text-sm text-center text-gray-800">{{ searchQuery }}</span>
                        <button @click="addPictogram(pic._id, searchQuery)" class="mt-3 bg-green-500 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-green-600 active:scale-95 transition-transform w-full">
                            Dodaj +
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showCategoryAlert" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" @click.self="showCategoryAlert = false">
            <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full text-center shadow-2xl transform transition-all">
                <div class="text-6xl mb-4">⚠️</div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">Brak kategorii</h3>
                <p class="text-gray-600 mb-8 font-medium leading-relaxed">
                    Zanim dodasz obrazek do swojej bazy, wybierz najpierw kategorię z rozwijanej listy na górze.
                </p>
                <button @click="showCategoryAlert = false" class="w-full py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-md active:scale-95">
                    OK, rozumiem
                </button>
            </div>
        </div>

        <div v-if="isFakeLoading" class="fixed inset-0 z-[200] flex flex-col items-center justify-center bg-white/90 backdrop-blur-md transition-all duration-300">
            <div class="text-7xl animate-bounce mb-6">📥</div>
            <h2 class="text-3xl font-black text-blue-600 mb-2">Pobieranie obrazka...</h2>
            <p class="text-gray-500 font-bold text-lg">Dodaję piktogram z bazy ARASAAC do Twojego konta!</p>
        </div>
    </AuthenticatedLayout>
</template>
