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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-10 bg-white/90 backdrop-blur-md p-4 sm:p-0 rounded-2xl shadow-sm sm:shadow-none sm:bg-transparent">
                <div class="flex items-center gap-4">
                    <Link :href="route('children.index')" class="text-gray-400 hover:text-blue-600 bg-gray-50 hover:bg-blue-50 w-10 h-10 flex items-center justify-center rounded-xl transition-colors">
                        <span class="text-xl font-bold">&larr;</span>
                    </Link>
                    <h2 class="font-black text-2xl text-gray-900 tracking-tight">
                        Słownik: <span class="text-blue-600">{{ child.name }}</span>
                    </h2>
                </div>
                <button @click="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 disabled:opacity-50">
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
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl p-6 sm:p-10 border border-gray-100">
                    <div v-if="filteredCategories.length === 0" class="text-center py-16 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        <span class="text-4xl mb-4 block">🔍</span>
                        <p class="text-gray-500 text-lg font-medium">Nie znaleziono żadnych piktogramów.</p>
                    </div>

                    <div v-for="category in filteredCategories" :key="category.id" class="mb-12 last:mb-0 border-b border-gray-100 last:border-0 pb-8 last:pb-0">

                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-8 h-[3px] rounded-full" :style="{ backgroundColor: category.color }"></span>
                            <h3 class="text-2xl font-black uppercase tracking-wider text-gray-900">
                                {{ category.name }}
                            </h3>
                        </div>

                        <div class="flex flex-wrap gap-4 sm:gap-6">

                            <div v-for="pictogram in category.pictograms" :key="pictogram.id"
                                 @click="togglePictogram(pictogram.id)"
                                 :class="[
                                     'border-4 rounded-3xl p-4 w-36 h-36 sm:w-44 sm:h-44 flex flex-col items-center justify-center cursor-pointer transition-all hover:-translate-y-1 relative group select-none',
                                     form.pictogram_ids.includes(pictogram.id) ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100' : 'border-gray-100 hover:border-gray-200 bg-white hover:bg-gray-50 grayscale opacity-50 hover:grayscale-0 hover:opacity-100'
                                 ]">

                                <div v-if="form.pictogram_ids.includes(pictogram.id)" class="absolute -top-3 -right-3 bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold shadow-md shadow-blue-200">
                                    ✓
                                </div>
                                <div v-else class="absolute -top-3 -right-3 bg-gray-200 text-gray-400 rounded-full w-8 h-8 flex items-center justify-center font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                    +
                                </div>

                                <img v-if="pictogram.image_path" :src="pictogram.image_path" @error="pictogram.image_path = null" :alt="pictogram.name" loading="lazy" class="w-16 h-16 sm:w-20 sm:h-20 object-contain mb-3 rounded pointer-events-none drop-shadow-sm" />
                                <div v-else class="text-5xl sm:text-6xl mb-3 pointer-events-none">🖼️</div>

                                <span class="text-sm sm:text-base font-bold text-gray-800 text-center leading-tight truncate w-full px-2 pointer-events-none">{{ pictogram.name }}</span>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
