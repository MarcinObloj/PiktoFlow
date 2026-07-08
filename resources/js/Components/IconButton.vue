<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    icon: { type: String, required: true },
    label: { type: String, required: true },
    variant: {
        type: String,
        default: 'ghost',
        validator: (v) => ['ghost', 'primary', 'danger', 'subtle'].includes(v),
    },
    size: { type: Number, default: 24 },
});

const variantClasses = computed(() => ({
    ghost: 'text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:text-gray-400 dark:hover:text-primary-300 dark:hover:bg-gray-700',
    subtle: 'text-gray-600 bg-gray-100 hover:bg-gray-200 dark:text-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600',
    primary: 'text-white bg-primary-600 hover:bg-primary-700 shadow-sm',
    danger: 'text-red-600 hover:text-white hover:bg-red-500 dark:text-red-400',
}[props.variant]));
</script>

<template>
    <button
        type="button"
        :aria-label="label"
        :title="label"
        class="inline-flex items-center justify-center w-10 h-10 rounded-xl transition-all active:scale-90 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary-500/40"
        :class="variantClasses"
    >
        <Icon :name="icon" :size="size" />
    </button>
</template>
