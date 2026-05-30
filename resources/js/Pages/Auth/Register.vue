<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.email = form.email.toLowerCase();
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Rejestracja" />

    <div class="min-h-screen flex w-full font-sans bg-gray-100">
        <div class="hidden md:flex md:w-1/2 bg-blue-600 justify-center items-center p-8 text-white shadow-inner">
            <div class="max-w-md text-center flex flex-col items-center">
                <Link :href="route('home')" class="group inline-block transition-transform hover:scale-105 active:scale-95 focus:outline-none">
                    <div class="text-8xl mb-8 drop-shadow-lg group-hover:rotate-12 transition-transform duration-300">🧩</div>
                    <h1 class="text-5xl font-black mb-4 tracking-tight">PiktoFlow</h1>
                </Link>
                <p class="text-xl font-light opacity-90 leading-relaxed mt-2">
                    Załóż darmowe konto opiekuna i stwórz pierwszą tablicę dla swojego dziecka.
                </p>
            </div>
        </div>

        <div class="w-full md:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-2xl border border-gray-50">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Dołącz do nas!</h2>
                <p class="text-gray-500 mb-8">Utwórz konto, aby rozpocząć.</p>

                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Imię i Nazwisko</label>
                        <input id="name" type="text" v-model="form.name" required autofocus placeholder="Jan Kowalski" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-gray-800 bg-gray-50 focus:bg-white" />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1 font-medium">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Adres Email</label>
                        <input id="email" type="email" v-model="form.email" required placeholder="jan@kowalski.pl" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-gray-800 bg-gray-50 focus:bg-white lowercase" />
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1 font-medium">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Hasło</label>
                        <input id="password" type="password" v-model="form.password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-gray-800 bg-gray-50 focus:bg-white" />
                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1 font-medium">{{ form.errors.password }}</div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1">Potwierdź hasło</label>
                        <input id="password_confirmation" type="password" v-model="form.password_confirmation" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-gray-800 bg-gray-50 focus:bg-white" />
                        <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1 font-medium">{{ form.errors.password_confirmation }}</div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-3 rounded-xl shadow-lg transition-all disabled:opacity-50 mt-2 active:scale-[0.98]">
                        {{ form.processing ? 'Tworzenie konta...' : 'Zarejestruj się' }}
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-600 bg-gray-50 p-4 rounded-xl">
                    Masz już konto?
                    <Link :href="route('login')" class="font-bold text-blue-600 hover:text-blue-800 transition-colors ml-1">
                        Zaloguj się
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
