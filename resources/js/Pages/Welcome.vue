<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isMenuOpen = ref(false);
</script>

<template>
    <Head title="PiktoFlow - Komunikacja bez barier" />

    <div class="min-h-screen bg-white font-sans text-gray-900 relative">
        <nav class="flex justify-between items-center p-6 max-w-7xl mx-auto relative z-50">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 p-2 rounded-xl shadow-lg">
                    <span class="text-2xl">🧩</span>
                </div>
                <span class="text-2xl font-black text-blue-600 tracking-tighter">PiktoFlow</span>
            </div>

            <div v-if="canLogin" class="hidden md:flex gap-4 items-center">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-bold text-gray-700 hover:text-blue-600 transition">Panel Sterowania</Link>
                <template v-else>
                    <Link :href="route('login')" class="font-bold text-gray-700 hover:text-blue-600 transition">Zaloguj się</Link>
                    <Link v-if="canRegister" :href="route('register')" class="bg-blue-600 text-white px-6 py-2 rounded-full font-bold shadow-md hover:bg-blue-700 transition active:scale-95">Załóż konto</Link>
                </template>
            </div>

            <button
                @click="isMenuOpen = !isMenuOpen"
                class="md:hidden p-2 text-gray-700 hover:text-blue-600 focus:outline-none transition"
                aria-label="Otwórz menu"
            >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!isMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </nav>

        <div
            v-show="isMenuOpen"
            class="md:hidden absolute top-20 left-0 w-full bg-white shadow-2xl border-t border-gray-100 z-40 px-6 py-6 flex flex-col gap-6 rounded-b-3xl"
        >
            <template v-if="canLogin">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-xl font-bold text-gray-700 hover:text-blue-600 transition">Panel Sterowania</Link>
                <template v-else>
                    <Link :href="route('login')" class="text-xl font-bold text-gray-700 hover:text-blue-600 transition text-center">Zaloguj się</Link>
                    <Link v-if="canRegister" :href="route('register')" class="bg-blue-600 text-white px-6 py-4 rounded-2xl font-bold shadow-md text-center hover:bg-blue-700 transition active:scale-95 text-lg">Załóż darmowe konto</Link>
                </template>
            </template>
        </div>

        <header class="max-w-7xl mx-auto px-6 pt-10 pb-20 md:pt-16 md:pb-24 flex flex-col lg:flex-row items-center gap-12 lg:gap-16">
            <div class="lg:w-1/2 text-center lg:text-left mt-8 lg:mt-0">
                <span class="bg-blue-100 text-blue-700 px-4 py-1 rounded-full text-sm font-bold uppercase tracking-widest mb-6 inline-block">System Wspomagający AAC</span>
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black leading-tight mb-6 text-gray-900">
                    Daj swojemu dziecku <span class="text-blue-600 italic underline decoration-blue-200">głos</span>.
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 mb-10 leading-relaxed max-w-lg mx-auto lg:mx-0 font-medium">
                    PiktoFlow to nowoczesna, darmowa platforma komunikacji alternatywnej. Twórz tablice, używaj bazy ARASAAC i buduj zdania w kilka sekund.
                </p>
                <div class="flex flex-col sm:flex-row flex-wrap gap-4 justify-center lg:justify-start">
                    <Link :href="route('register')" class="bg-blue-600 text-white px-8 py-4 rounded-2xl text-lg sm:text-xl font-bold shadow-xl hover:bg-blue-700 transition hover:-translate-y-1 active:scale-95 w-full sm:w-auto text-center">
                        Rozpocznij teraz
                    </Link>
                    <a href="#funkcje" class="bg-gray-100 text-gray-700 px-8 py-4 rounded-2xl text-lg sm:text-xl font-bold hover:bg-gray-200 transition w-full sm:w-auto text-center">
                        Zobacz funkcje
                    </a>
                </div>
            </div>

            <div class="lg:w-1/2 w-full relative max-w-md mx-auto lg:max-w-none mt-10 lg:mt-0">
                <div class="bg-blue-600 w-full aspect-square rounded-[3rem] rotate-3 absolute -z-10 opacity-10"></div>
                <div class="bg-white border-4 sm:border-8 border-gray-900 rounded-[2rem] sm:rounded-[3rem] shadow-2xl overflow-hidden transform -rotate-2 hover:rotate-0 transition duration-500">
                    <div class="bg-gray-100 p-3 sm:p-4 border-b-2 sm:border-b-4 border-gray-900 flex gap-2">
                        <div class="w-3 h-3 sm:w-4 sm:h-4 bg-red-400 rounded-full"></div>
                        <div class="w-3 h-3 sm:w-4 sm:h-4 bg-yellow-400 rounded-full"></div>
                        <div class="w-3 h-3 sm:w-4 sm:h-4 bg-green-400 rounded-full"></div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-wrap gap-2 mb-6">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-blue-100 rounded-xl flex items-center justify-center text-2xl sm:text-3xl">Ja</div>
                            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-green-100 rounded-xl flex items-center justify-center text-2xl sm:text-3xl">Chcę</div>
                            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-yellow-100 rounded-xl flex items-center justify-center text-2xl sm:text-3xl">🍎</div>
                        </div>
                        <div class="grid grid-cols-4 gap-2 sm:gap-3 opacity-50">
                            <div v-for="i in 8" :key="i" class="aspect-square bg-gray-200 rounded-lg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section id="funkcje" class="bg-gray-50 py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="text-3xl sm:text-4xl font-black mb-4">Wszystko, czego potrzebujesz</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto text-base sm:text-lg">Zaprojektowane z myślą o terapeutach, rodzicach i przede wszystkim – użytkownikach AAC.</p>
                </div>

                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
                    <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                        <div class="text-4xl sm:text-5xl mb-4 sm:mb-6 group-hover:scale-110 transition duration-300">🗣️</div>
                        <h3 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4">Budowanie Zdań</h3>
                        <p class="text-gray-600 leading-relaxed text-sm sm:text-base">Pozwól dziecku łączyć piktogramy w pełne myśli. Syntezator mowy odczyta całe zdanie jednym kliknięciem.</p>
                    </div>

                    <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                        <div class="text-4xl sm:text-5xl mb-4 sm:mb-6 group-hover:scale-110 transition duration-300">🖼️</div>
                        <h3 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4">Baza ARASAAC</h3>
                        <p class="text-gray-600 leading-relaxed text-sm sm:text-base">Automatyczny dostęp do tysięcy ustandaryzowanych piktogramów medycznych. Dodawaj też własne zdjęcia.</p>
                    </div>

                    <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                        <div class="text-4xl sm:text-5xl mb-4 sm:mb-6 group-hover:scale-110 transition duration-300">👶</div>
                        <h3 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4">Profile Dzieci</h3>
                        <p class="text-gray-600 leading-relaxed text-sm sm:text-base">Zarządzaj wieloma profilami. Każde dziecko może mieć własną, spersonalizowaną tablicę słów.</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-8 sm:py-12 text-center border-t border-gray-100">
            <p class="text-gray-400 font-medium text-sm sm:text-base">© 2026 PiktoFlow. Projekt stworzony z myślą o wsparciu komunikacji.</p>
        </footer>
    </div>
</template>

<style scoped>
html {
    scroll-behavior: smooth;
}
</style>
