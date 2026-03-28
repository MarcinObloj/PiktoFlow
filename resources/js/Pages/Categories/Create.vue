<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    color: '#3B82F6',
});

const isFakeLoading = ref(false);

const submit = () => {
    isFakeLoading.value = true;
    setTimeout(() => {
        form.post(route('categories.store'), {
            onFinish: () => {
                isFakeLoading.value = false;
            }
        });
    }, 1500);
};
</script>

<template>
    <Head title="Nowa Kategoria" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('dashboard')" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&larr;</Link>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Dodaj własną kategorię</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8 relative">
                <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl shadow-sm border-4 border-gray-50" :style="{ backgroundColor: form.color }">
                            📁
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900">Kreator Kategorii</h3>
                            <p class="text-gray-500 text-sm">Wybierz nazwę i kolor, aby łatwiej grupować słowa.</p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nazwa Kategorii (np. Moje Zabawki)</label>
                            <input
                                type="text"
                                v-model="form.name"
                                required
                                autofocus
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all bg-gray-50 focus:bg-white text-lg font-medium"
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Kolor Kategorii</label>
                            <div class="flex items-center gap-4">
                                <input
                                    type="color"
                                    v-model="form.color"
                                    class="h-14 w-24 cursor-pointer rounded-xl border-2 border-gray-200"
                                />
                                <span class="text-gray-500 font-mono font-bold">{{ form.color }}</span>
                            </div>
                            <div v-if="form.errors.color" class="text-red-500 text-sm mt-1">{{ form.errors.color }}</div>
                        </div>

                        <button
                            type="submit"
                            :disabled="isFakeLoading || form.processing"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-4 rounded-xl shadow-lg transition-all disabled:opacity-50 mt-4 active:scale-95"
                        >
                            Zapisz kategorię
                        </button>
                    </form>

                </div>
            </div>
        </div>

        <div v-if="isFakeLoading || form.processing" class="fixed inset-0 z-[200] flex flex-col items-center justify-center bg-white/90 backdrop-blur-md transition-all duration-300">
            <div class="text-7xl animate-bounce mb-6">📁</div>
            <h2 class="text-3xl font-black text-blue-600 mb-2">Tworzenie kategorii...</h2>
            <p class="text-gray-500 font-bold text-lg">Zapisuję nową grupę słów w Twojej bibliotece!</p>
        </div>

    </AuthenticatedLayout>
</template>
