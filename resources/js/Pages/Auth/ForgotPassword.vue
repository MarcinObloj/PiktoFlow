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
    <Head title="Resetowanie hasła" />

    <div class="min-h-screen flex w-full font-sans bg-gray-100">
        <div class="hidden md:flex md:w-1/2 bg-blue-600 justify-center items-center p-8 text-white shadow-inner">
            <div class="max-w-md text-center flex flex-col items-center">
                <Link :href="route('home')" class="group inline-block transition-transform hover:scale-105 active:scale-95 focus:outline-none">
                    <div class="text-8xl mb-8 drop-shadow-lg group-hover:rotate-12 transition-transform duration-300">🧩</div>
                    <h1 class="text-5xl font-black mb-4 tracking-tight">PiktoFlow</h1>
                </Link>
                <p class="text-xl font-light opacity-90 leading-relaxed mt-2">
                    Nie martw się — pomożemy Ci odzyskać dostęp do konta.
                </p>
            </div>
        </div>

        <div class="w-full md:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-2xl border border-gray-50">

                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Zapomniane hasło?</h2>
                <p class="text-gray-500 mb-6 text-center text-sm">
                    Podaj swój adres email, a wyślemy Ci link do resetowania hasła.
                </p>

                <div v-if="status" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium text-center">
                    ✅ Wysłaliśmy link do resetowania hasła na Twój adres email.
                </div>

                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Adres Email</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            placeholder="jan@kowalski.pl"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-gray-800 bg-gray-50 focus:bg-white lowercase"
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1 font-medium">{{ form.errors.email }}</div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-3 rounded-xl shadow-lg transition-all disabled:opacity-50 mt-2 active:scale-[0.98]"
                    >
                        {{ form.processing ? 'Wysyłanie...' : 'Wyślij link resetujący' }}
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <Link :href="route('login')" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">
                        ← Powrót do logowania
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
