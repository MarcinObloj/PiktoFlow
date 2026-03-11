<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-bold text-gray-900">Bezpieczeństwo</h2>
            <p class="mt-1 text-sm text-gray-600">Upewnij się, że Twoje konto używa długiego, losowego hasła.</p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Obecne hasło</label>
                <input
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all bg-gray-50 focus:bg-white"
                />
                <div v-if="form.errors.current_password" class="text-red-500 text-sm mt-1">{{ form.errors.current_password }}</div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nowe hasło</label>
                <input
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all bg-gray-50 focus:bg-white"
                />
                <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Potwierdź nowe hasło</label>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all bg-gray-50 focus:bg-white"
                />
                <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ form.errors.password_confirmation }}</div>
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-xl shadow transition disabled:opacity-50"
                >
                    Zaktualizuj hasło
                </button>
                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600 font-bold italic">Hasło zmienione!</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
