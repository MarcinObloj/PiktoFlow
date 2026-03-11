<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Logowanie" />

    <div class="min-h-screen flex w-full font-sans bg-gray-100">

        <div class="hidden md:flex md:w-1/2 bg-blue-600 justify-center items-center p-8 text-white shadow-inner">
            <div class="max-w-md text-center">
                <div class="text-8xl mb-8 drop-shadow-lg">🧩</div>
                <h1 class="text-5xl font-black mb-4 tracking-tight">PiktoFlow</h1>
                <p class="text-xl font-light opacity-90 leading-relaxed">
                    Twój system komunikacji alternatywnej (AAC). Pomagamy wyrazić to, co najważniejsze.
                </p>
            </div>
        </div>

        <div class="w-full md:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-2xl border border-gray-50">

                <h2 class="text-3xl font-bold text-gray-900 mb-2">Witaj ponownie!</h2>
                <p class="text-gray-500 mb-8">Zaloguj się do panelu opiekuna.</p>

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 p-3 bg-green-50 rounded-lg">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="flex flex-col gap-5">

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Adres Email</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            placeholder="jan@kowalski.pl"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-gray-800 bg-gray-50 focus:bg-white"
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1 font-medium">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label for="password" class="block text-sm font-bold text-gray-700">Hasło</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                Zapomniałeś hasła?
                            </Link>
                        </div>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-gray-800 bg-gray-50 focus:bg-white"
                        />
                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1 font-medium">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center mt-2">
                        <input
                            id="remember"
                            type="checkbox"
                            v-model="form.remember"
                            class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer"
                        />
                        <label for="remember" class="ml-2 text-sm text-gray-700 font-medium cursor-pointer">Zapamiętaj mnie na tym urządzeniu</label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-3 rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 mt-4 active:scale-[0.98]"
                    >
                        Zaloguj się
                    </button>

                </form>

                <div class="mt-8 text-center text-sm text-gray-600 bg-gray-50 p-4 rounded-xl">
                    Nie masz jeszcze konta?
                    <Link :href="route('register')" class="font-bold text-blue-600 hover:text-blue-800 transition-colors ml-1">
                        Zarejestruj się za darmo
                    </Link>
                </div>

            </div>
        </div>

    </div>
</template>
