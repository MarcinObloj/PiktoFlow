<script setup>
defineProps({
    // Adds hover elevation + subtle lift for interactive cards.
    interactive: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="bg-white dark:bg-surface-dark-muted border border-gray-100 dark:border-gray-700 rounded-card shadow-card transition-all"
        :class="interactive ? 'hover:shadow-md hover:-translate-y-0.5' : ''"
    >
        <slot />
    </div>
</template>
