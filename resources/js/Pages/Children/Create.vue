<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('children.store'));
};
</script>

<template>
    <Head title="Dodaj Profil Dziecka" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dodaj nowe dziecko</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="flex flex-col gap-4">

                        <div>
                            <label class="block font-medium text-gray-700">Imię dziecka</label>
                            <input type="text" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required autofocus />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <Link :href="route('children.index')" class="px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300">Anuluj</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 rounded-md text-white hover:bg-blue-700 disabled:opacity-50">
                                Zapisz profil
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
