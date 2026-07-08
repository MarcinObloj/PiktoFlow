<script setup>
import { computed } from 'vue';
import BaseModal from '@/Components/BaseModal.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Potwierdź' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Potwierdź' },
    cancelLabel: { type: String, default: 'Anuluj' },
    icon: { type: String, default: 'alert' },
    variant: {
        type: String,
        default: 'danger',
        validator: (v) => ['danger', 'primary'].includes(v),
    },
    processing: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel']);

const accent = computed(() =>
    props.variant === 'danger'
        ? {
              ring: 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300',
              btn: 'bg-red-500 hover:bg-red-600 shadow-red-200',
          }
        : {
              ring: 'bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300',
              btn: 'bg-primary-600 hover:bg-primary-700 shadow-primary-200',
          }
);
</script>

<template>
    <BaseModal :show="show" max-width="md" @close="emit('cancel')">
        <div class="p-8 text-center">
            <div
                class="mx-auto mb-6 w-16 h-16 rounded-2xl flex items-center justify-center"
                :class="accent.ring"
            >
                <Icon :name="icon" :size="32" />
            </div>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">{{ title }}</h3>
            <p v-if="message" class="text-gray-600 dark:text-gray-300 mb-8 font-medium leading-relaxed">
                {{ message }}
            </p>
            <slot />
            <div class="grid grid-cols-2 gap-4 mt-2">
                <button
                    type="button"
                    @click="emit('cancel')"
                    class="py-4 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition active:scale-95"
                >
                    {{ cancelLabel }}
                </button>
                <button
                    type="button"
                    :disabled="processing"
                    @click="emit('confirm')"
                    class="py-4 text-white font-bold rounded-xl transition shadow-lg active:scale-95 disabled:opacity-50"
                    :class="accent.btn"
                >
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </BaseModal>
</template>
