<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    category_id: '',
    image: null,
});

const submit = () => {
    form.post(route('pictograms.store'));
};
</script>

<template>
    <Head title="Dodaj Piktogram" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dodaj nowy piktogram</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="flex flex-col gap-4">

                        <div>
                            <label class="block font-medium text-gray-700">Nazwa (np. Pies)</label>
                            <input type="text" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700">Kategoria</label>
                            <select v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option disabled value="">Wybierz kategorię...</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700">Zdjęcie</label>
                            <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="mt-1 block w-full" required />
                            <div v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <Link :href="route('dashboard')" class="px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300">Anuluj</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 rounded-md text-white hover:bg-blue-700 disabled:opacity-50">
                                Zapisz piktogram
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
