<script setup>
import { useForm } from '@inertiajs/vue3';

defineProps({
    hasBoardPin: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    board_pin: '',
});

const updatePin = () => {
    form.patch(route('profile.pin.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-bold text-gray-900">Blokada rodzicielska (PIN)</h2>
            <p class="mt-1 text-sm text-gray-600">
                4-cyfrowy kod zabezpieczający wyjście dziecka z tablicy komunikacyjnej.
                <span v-if="hasBoardPin" class="font-bold text-green-600">Własny PIN jest ustawiony.</span>
                <span v-else class="font-bold text-amber-600">Używany jest domyślny kod 1234 — ustaw własny.</span>
            </p>
        </header>

        <form @submit.prevent="updatePin" class="mt-6 space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nowy kod PIN</label>
                <input
                    v-model="form.board_pin"
                    type="password"
                    inputmode="numeric"
                    maxlength="4"
                    autocomplete="new-password"
                    placeholder="np. 4821"
                    class="w-40 text-center text-2xl tracking-[0.5em] px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all bg-gray-50 focus:bg-white"
                />
                <div v-if="form.errors.board_pin" class="text-red-500 text-sm mt-1">{{ form.errors.board_pin }}</div>
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-6 rounded-xl shadow transition disabled:opacity-50"
                >
                    Zapisz PIN
                </button>
                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600 font-bold italic">PIN zapisany!</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
