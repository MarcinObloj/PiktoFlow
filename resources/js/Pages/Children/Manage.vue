<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    child: Object,
    categories: Array,
    activePictogramIds: Array,
});

const form = useForm({
    pictogram_ids: props.activePictogramIds || [],
});

const searchQuery = ref('');

const filteredCategories = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.categories;
    }

    const query = searchQuery.value.toLowerCase().trim();

    return props.categories.map(category => {
        const filteredPictograms = category.pictograms.filter(p =>
            p.name.toLowerCase().includes(query)
        );


        return {
            ...category,
            pictograms: filteredPictograms
        };
    }).filter(category => category.pictograms.length > 0 || category.name.toLowerCase().includes(query));
});

const togglePictogram = (id) => {
    const index = form.pictogram_ids.indexOf(id);
    if (index === -1) {
        form.pictogram_ids.push(id);
    } else {
        form.pictogram_ids.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('children.update-words', props.child.id));
};
</script>

<template>
    <Head :title="'Zarządzaj - ' + child.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('children.index')" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">
                        &larr;
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Słownik dla: <span class="text-purple-600">{{ child.name }}</span>
                    </h2>
                </div>
                <button @click="submit" :disabled="form.processing" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded shadow transition disabled:opacity-50">
                    Zapisz zmiany
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded shadow-sm">
                    <p class="text-blue-700">
                        <strong>Instrukcja:</strong> Kliknij piktogram, aby dodać go do tablicy dziecka (podświetli się na zielono). Ponowne kliknięcie usunie go z tablicy. Pamiętaj, aby po wszystkim kliknąć "Zapisz zmiany" w prawym górnym rogu.
                    </p>
                </div>

                <div class="mb-8">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center justify-center pointer-events-none">
                            <span class="text-gray-400 text-xl leading-none">🔍</span>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="block w-full pl-12 pr-4 py-4 rounded-2xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-lg"
                            placeholder="Wyszukaj słowo (np. jabłko, mama, pić)..."
                        />
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div v-if="filteredCategories.length === 0" class="text-center py-12 text-gray-500 text-lg">
                        Nie znaleziono żadnych piktogramów pasujących do wyszukiwania.
                    </div>

                    <div v-for="category in filteredCategories" :key="category.id" class="mb-8 border-b pb-4">

                        <h3 class="text-2xl font-bold mb-4 uppercase tracking-wider" :style="{ color: category.color }">
                            {{ category.name }}
                        </h3>

                        <div class="flex flex-wrap gap-4">

                            <div v-for="pictogram in category.pictograms" :key="pictogram.id"
                                 @click="togglePictogram(pictogram.id)"
                                 :class="[
                                     'border-4 rounded-2xl p-4 w-40 h-40 flex flex-col items-center justify-center cursor-pointer transition-all shadow-md hover:-translate-y-1 relative',
                                     form.pictogram_ids.includes(pictogram.id) ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:bg-gray-100 grayscale opacity-60 hover:grayscale-0 hover:opacity-100'
                                 ]">

                                <div v-if="form.pictogram_ids.includes(pictogram.id)" class="absolute top-2 right-2 bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold text-sm shadow">
                                    ✓
                                </div>

                                <img v-if="pictogram.image_path" :src="pictogram.image_path" @error="pictogram.image_path = null" :alt="pictogram.name" class="w-16 h-16 object-contain mb-2 rounded" />
                                <div v-else class="text-5xl mb-2">🖼️</div>

                                <span class="text-lg font-bold text-gray-800 text-center leading-tight">{{ pictogram.name }}</span>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
