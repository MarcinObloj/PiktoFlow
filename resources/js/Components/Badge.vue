<script setup>
import { computed } from 'vue';

const props = defineProps({
    color: {
        type: String,
        default: 'gray',
        validator: (v) => ['gray', 'primary', 'green', 'amber', 'red', 'purple'].includes(v),
    },
});

const colorClasses = computed(() => ({
    gray: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
    primary: 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-200',
    green: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200',
    amber: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200',
    purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-200',
}[props.color]));
</script>

<template>
    <span
        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase"
        :class="colorClasses"
    >
        <slot />
    </span>
</template>
