<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <Head title="Weryfikacja adresu email" />

    <div class="min-h-screen flex w-full font-sans bg-gray-100">
        <div class="hidden md:flex md:w-1/2 bg-blue-600 justify-center items-center p-8 text-white shadow-inner">
            <div class="max-w-md text-center flex flex-col items-center">
                <Link :href="route('home')" class="group inline-block transition-transform hover:scale-105 active:scale-95 focus:outline-none">
                    <div class="text-8xl mb-8 drop-shadow-lg group-hover:rotate-12 transition-transform duration-300">🧩</div>
                    <h1 class="text-5xl font-black mb-4 tracking-tight">PiktoFlow</h1>
                </Link>
                <p class="text-xl font-light opacity-90 leading-relaxed mt-2">
                    Jeden krok dzieli Cię od tablicy dla Twojego dziecka.
                </p>
            </div>
        </div>

        <div class="w-full md:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-2xl border border-gray-50">

                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Potwierdź email</h2>
                <p class="text-gray-500 mb-6 text-center">
                    Konto zostało utworzone! Wysłaliśmy link weryfikacyjny na Twój adres email.
                    Kliknij w link, aby aktywować konto.
                </p>

                <div
                    v-if="verificationLinkSent"
                    class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium text-center"
                >
                    ✅ Nowy link weryfikacyjny został wysłany na Twój adres email.
                </div>

                <form @submit.prevent="submit">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg py-3 rounded-xl shadow-lg transition-all disabled:opacity-50 active:scale-[0.98]"
                    >
                        {{ form.processing ? 'Wysyłanie...' : 'Wyślij link ponownie' }}
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        Wyloguj się
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
