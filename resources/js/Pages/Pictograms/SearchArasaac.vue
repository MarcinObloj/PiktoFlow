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
        alert("Wybierz najpierw kategorię!");
        return;
    }

    router.post(route('pictograms.store'), {
        name: name,
        category_id: selectedCategoryId.value,
        image_url: `https://static.arasaac.org/pictograms/${id}/${id}_300.png`
    });
};
</script>

<template>
    <Head title="Szukaj w ARASAAC" />
    <AuthenticatedLayout>
        <div class="py-12 px-4 max-w-5xl mx-auto">
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

                <div v-if="loading" class="text-center py-10">Szukanie...</div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    <div v-for="pic in results" :key="pic._id" class="border border-gray-100 p-4 rounded-2xl flex flex-col items-center hover:bg-blue-50 transition group relative">
                        <img :src="`https://static.arasaac.org/pictograms/${pic._id}/${pic._id}_300.png`" class="w-24 h-24 object-contain mb-3" />
                        <span class="font-bold text-sm text-center">{{ searchQuery }}</span>
                        <button @click="addPictogram(pic._id, searchQuery)" class="mt-3 bg-green-500 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-green-600">
                            Dodaj +
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
