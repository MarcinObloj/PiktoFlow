<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="PiktoFlow - Komunikacja bez barier" />

    <div class="min-h-screen bg-white font-sans text-gray-900">
        <nav class="flex justify-between items-center p-6 max-w-7xl mx-auto">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 p-2 rounded-xl shadow-lg">
                    <span class="text-2xl">🧩</span>
                </div>
                <span class="text-2xl font-black text-blue-600 tracking-tighter">PiktoFlow</span>
            </div>

            <div v-if="canLogin" class="flex gap-4 items-center">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-bold text-gray-700 hover:text-blue-600 transition">Panel Sterowania</Link>
                <template v-else>
                    <Link :href="route('login')" class="font-bold text-gray-700 hover:text-blue-600 transition">Zaloguj się</Link>
                    <Link v-if="canRegister" :href="route('register')" class="bg-blue-600 text-white px-6 py-2 rounded-full font-bold shadow-md hover:bg-blue-700 transition active:scale-95">Załóż konto</Link>
                </template>
            </div>
        </nav>

        <header class="max-w-7xl mx-auto px-6 pt-16 pb-24 flex flex-col lg:flex-row items-center gap-12">
            <div class="lg:w-1/2">
                <span class="bg-blue-100 text-blue-700 px-4 py-1 rounded-full text-sm font-bold uppercase tracking-widest mb-4 inline-block">System Wspomagający AAC</span>
                <h1 class="text-6xl lg:text-7xl font-black leading-tight mb-6 text-gray-900">
                    Daj swojemu dziecku <span class="text-blue-600 italic underline decoration-blue-200">głos</span>.
                </h1>
                <p class="text-xl text-gray-600 mb-10 leading-relaxed max-w-lg font-medium">
                    PiktoFlow to nowoczesna, darmowa platforma komunikacji alternatywnej. Twórz tablice, używaj bazy ARASAAC i buduj zdania w kilka sekund.
                </p>
                <div class="flex flex-wrap gap-4">
                    <Link :href="route('register')" class=" bg-blue-600 text-white px-10 py-4 px-2 rounded-2xl text-xl font-bold shadow-xl hover:bg-blue-700 transition hover:-translate-y-1 active:scale-95">Rozpocznij teraz</Link>
                    <a href="#funkcje" class="bg-gray-100 text-gray-700 px-10 py-4 px-2 nprounded-2xl text-xl font-bold hover:bg-gray-200 transition">Zobacz funkcje</a>
                </div>
            </div>

            <div class="lg:w-1/2 relative">
                <div class="bg-blue-600 w-full aspect-square rounded-[3rem] rotate-3 absolute -z-10 opacity-10"></div>
                <div class="bg-white border-8 border-gray-900 rounded-[3rem] shadow-2xl overflow-hidden transform -rotate-2 hover:rotate-0 transition duration-500">
                    <div class="bg-gray-100 p-4 border-b-4 border-gray-900 flex gap-2">
                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    </div>
                    <div class="p-6">
                        <div class="flex gap-2 mb-6">
                            <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center text-3xl">Ja</div>
                            <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center text-3xl">Chcę</div>
                            <div class="w-16 h-16 bg-yellow-100 rounded-xl flex items-center justify-center text-3xl">🍎</div>
                        </div>
                        <div class="grid grid-cols-4 gap-3 opacity-50">
                            <div v-for="i in 8" :key="i" class="aspect-square bg-gray-200 rounded-lg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section id="funkcje" class="bg-gray-50 py-24">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-black mb-4">Wszystko, czego potrzebujesz</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto text-lg">Zaprojektowane z myślą o terapeutach, rodzicach i przede wszystkim – użytkownikach AAC.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                        <div class="text-5xl mb-6 group-hover:scale-110 transition duration-300">🗣️</div>
                        <h3 class="text-2xl font-bold mb-4">Budowanie Zdań</h3>
                        <p class="text-gray-600 leading-relaxed">Pozwól dziecku łączyć piktogramy w pełne myśli. Syntezator mowy odczyta całe zdanie jednym kliknięciem.</p>
                    </div>

                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                        <div class="text-5xl mb-6 group-hover:scale-110 transition duration-300">🖼️</div>
                        <h3 class="text-2xl font-bold mb-4">Baza ARASAAC</h3>
                        <p class="text-gray-600 leading-relaxed">Automatyczny dostęp do tysięcy ustandaryzowanych piktogramów medycznych. Dodawaj też własne zdjęcia.</p>
                    </div>

                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                        <div class="text-5xl mb-6 group-hover:scale-110 transition duration-300">👶</div>
                        <h3 class="text-2xl font-bold mb-4">Profile Dzieci</h3>
                        <p class="text-gray-600 leading-relaxed">Zarządzaj wieloma profilami. Każde dziecko może mieć własną, spersonalizowaną tablicę słów.</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-12 text-center border-t border-gray-100">
            <p class="text-gray-400 font-medium">© 2026 PiktoFlow. Projekt stworzony z myślą o wsparciu komunikacji.</p>
        </footer>
    </div>
</template>

<style scoped>

html {
    scroll-behavior: smooth;
}
</style>
