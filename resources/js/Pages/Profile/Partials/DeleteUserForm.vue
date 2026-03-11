<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-xl font-bold text-red-700">Usuń konto</h2>
            <p class="mt-1 text-sm text-red-600 italic font-medium">Uwaga: Wszystkie dane dzieci i piktogramy zostaną bezpowrotnie usunięte.</p>
        </header>

        <button
            @click="confirmingUserDeletion = true"
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-xl shadow transition"
        >
            Chcę usunąć konto
        </button>

        <div v-if="confirmingUserDeletion" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="bg-white p-8 rounded-3xl max-w-md w-full shadow-2xl">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 text-center">Jesteś pewien? 🛑</h2>
                <p class="text-gray-600 mb-6 text-center italic">Proszę wpisz hasło, aby potwierdzić trwałe usunięcie konta.</p>

                <input
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    placeholder="Twoje hasło"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 outline-none mb-4 bg-gray-50"
                    @keyup.enter="deleteUser"
                />

                <div v-if="form.errors.password" class="text-red-500 text-sm mb-4">{{ form.errors.password }}</div>

                <div class="flex justify-center gap-3">
                    <button @click="closeModal" class="px-6 py-2 bg-gray-200 rounded-xl font-bold text-gray-700">Anuluj</button>
                    <button @click="deleteUser" :disabled="form.processing" class="px-6 py-2 bg-red-600 rounded-xl font-bold text-white shadow hover:bg-red-700">USUŃ KONTO</button>
                </div>
            </div>
        </div>
    </section>
</template>
