<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    template: Object,
    children: Array,
});

const form = useForm({ child_id: '' });
const success = ref(false);

const apply = () => {
    form.post(route('templates.apply', props.template.id), {
        onSuccess: () => { success.value = true; },
    });
};
</script>

<template>
    <Head :title="template.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="text-3xl">{{ template.icon }}</span>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ template.name }}</h2>
            </div>
        </template>

        <div class="py-8 max-w-5xl mx-auto px-4">

            <!-- Success -->
            <div v-if="success" class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-300 font-medium text-center">
                ✅ Szablon został przypisany do dziecka!
            </div>

            <div class="bg-white dark:bg-surface-dark-muted rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 mb-8">
                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-4">📌 Przypisz do dziecka</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">{{ template.description }}</p>

                <div v-if="children.length === 0" class="text-gray-400 dark:text-gray-500 text-sm">
                    Nie masz jeszcze żadnego dziecka. <a :href="route('children.create')" class="text-primary-600 dark:text-primary-400 font-medium hover:underline">Dodaj dziecko</a>
                </div>
                <div v-else class="flex flex-col sm:flex-row gap-3">
                    <select
                        v-model="form.child_id"
                        class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-900 outline-none bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100"
                    >
                        <option value="" disabled>-- Wybierz dziecko --</option>
                        <option v-for="child in children" :key="child.id" :value="child.id">{{ child.name }}</option>
                    </select>
                    <button
                        @click="apply"
                        :disabled="!form.child_id || form.processing"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow transition-all disabled:opacity-40 whitespace-nowrap"
                    >
                        {{ form.processing ? 'Przypisywanie...' : '✨ Przypisz zestaw' }}
                    </button>
                </div>
            </div>

            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-4">Piktogramy w zestawie ({{ template.items.length }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <div
                    v-for="item in template.items"
                    :key="item.id"
                    class="bg-white dark:bg-surface-dark-muted rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-3 flex flex-col items-center gap-2 hover:shadow-md transition-shadow"
                >
                    <img
                        :src="item.image_path"
                        :alt="item.name"
                        class="w-20 h-20 object-contain"
                        loading="lazy"
                    />
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 text-center">{{ item.name }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ item.category_name }}</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
