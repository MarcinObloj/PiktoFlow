<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    children: Array,
});
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
                        <div v-for="child in children" :key="child.id" class="border-2 border-gray-200 rounded-xl p-6 flex flex-col items-center hover:border-blue-400 transition shadow-sm">
                            <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center text-4xl mb-4">
                                👦
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">{{ child.name }}</h3>

                            <div class="flex gap-2 w-full">
                                <Link :href="route('children.board', child.id)" class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 transition font-semibold">
                                    Tablica (AAC)
                                </Link>
                                <Link :href="route('children.manage', child.id)" class="flex-1 bg-purple-500 text-white text-center py-2 rounded hover:bg-purple-600 transition font-semibold">
                                    Zarządzaj słowami
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
