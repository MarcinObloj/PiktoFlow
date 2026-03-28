<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    age: '',
    hobbies: '',
});

const submit = () => {
    form.post(route('children.store'));
};
</script>

<template>
    <Head title="Dodaj Profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">👶 Dodaj profil dziecka</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl p-8 border border-gray-100">
                    <form @submit.prevent="submit" class="flex flex-col gap-6">

                        <div>
                            <label class="block font-bold text-gray-700 text-lg mb-2">Imię dziecka</label>
                            <input type="text" v-model="form.name" class="block w-full rounded-2xl border-gray-300 shadow-sm focus:border-blue-500 py-3 px-4" required />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-2">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 text-lg mb-2">Wiek (opcjonalnie)</label>
                            <input type="number" v-model="form.age" class="block w-full rounded-2xl border-gray-300 shadow-sm focus:border-blue-500 py-3 px-4" min="1" max="99" />
                            <div v-if="form.errors.age" class="text-red-500 text-sm mt-2">{{ form.errors.age }}</div>
                        </div>

                        <div class="bg-purple-50 p-6 rounded-2xl border border-purple-100">
                            <label class="block font-bold text-purple-900 text-lg mb-2">🤖 Magiczne Hobby (opcjonalnie)</label>
                            <p class="text-sm text-purple-700 mb-4">Wpisz po przecinku, np: <b>kot, pociąg, muzyka</b>. System automatycznie wyszuka obrazki i doda je do tablicy!</p>
                            <input type="text" v-model="form.hobbies" placeholder="np. rower, jabłko, auto" class="block w-full rounded-2xl border-purple-300 shadow-sm focus:border-purple-500 py-3 px-4" />
                            <div v-if="form.errors.hobbies" class="text-red-500 text-sm mt-2">{{ form.errors.hobbies }}</div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                            <Link :href="route('children.index')" class="px-6 py-4 bg-gray-100 font-bold rounded-2xl text-gray-700 hover:bg-gray-200">
                                Anuluj
                            </Link>
                            <button type="submit" :disabled="form.processing" class="px-6 py-4 bg-blue-600 font-bold rounded-2xl text-white hover:bg-blue-700 disabled:opacity-50">
                                {{ form.processing ? 'Szukam obrazków i zapisuję...' : 'Zapisz profil' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
