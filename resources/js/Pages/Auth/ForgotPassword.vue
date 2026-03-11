<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Zapomniane hasło" />

    <div class="min-h-screen flex items-center justify-center bg-gray-100 font-sans p-6">
        <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-2xl border border-gray-50">
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🔐</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Zapomniałeś hasła?</h2>
                <p class="text-gray-500 text-sm">Nie ma problemu. Podaj swój adres email, a wyślemy Ci link do ustawienia nowego hasła.</p>
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600 p-3 bg-green-50 rounded-lg text-center">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Adres Email</label>
                    <input id="email" type="email" v-model="form.email" required autofocus placeholder="jan@kowalski.pl" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-gray-800 bg-gray-50 focus:bg-white" />
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1 font-medium">{{ form.errors.email }}</div>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition-all disabled:opacity-50 mt-2">
                    Wyślij link do resetu
                </button>

                <div class="text-center mt-4">
                    <Link :href="route('login')" class="text-sm font-bold text-gray-600 hover:text-blue-600 transition-colors">
                        Wróć do logowania
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
