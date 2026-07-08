<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    templates: Array,
});
</script>

<template>
    <Head title="Szablony piktogramów" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Gotowe zestawy piktogramów</h2>
            </div>
        </template>

        <div class="py-8 max-w-6xl mx-auto px-4">
            <p class="text-gray-500 dark:text-gray-400 mb-8 text-base">Wybierz gotowy zestaw i przypisz go do dziecka jednym kliknięciem.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="tmpl in templates"
                    :key="tmpl.id"
                    :href="route('templates.show', tmpl.id)"
                    class="group bg-white dark:bg-surface-dark-muted rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 flex flex-col gap-3 hover:shadow-xl hover:-translate-y-1 transition-all duration-200"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-3xl shadow-sm" :style="{ backgroundColor: tmpl.color + '22' }">
                            {{ tmpl.icon }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ tmpl.name }}</h3>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ tmpl.items_count }} piktogramów</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ tmpl.description }}</p>
                    <div class="mt-auto pt-2">
                        <span class="inline-block text-sm font-semibold px-3 py-1 rounded-full text-white" :style="{ backgroundColor: tmpl.color }">
                            Podgląd i przypisz →
                        </span>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
