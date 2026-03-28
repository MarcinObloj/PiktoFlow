<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    child: Object,
    availablePictograms: Array,
});

const selectedPlan = ref(
    (props.child.daily_plan || [])
        .map(id => props.availablePictograms.find(p => p.id === id))
        .filter(Boolean)
);

const availableOptions = computed(() => {
    return props.availablePictograms.filter(
        p => !selectedPlan.value.find(sp => sp.id === p.id)
    );
});

const saveSchedule = () => {
    const ids = selectedPlan.value.map(p => p.id);
    router.post(route('children.update-schedule', props.child.id), { schedule: ids });
};
</script>

<template>
    <Head :title="'Plan Dnia: ' + child.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                🗓️ Plan Dnia dla: {{ child.name }}
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">

                    <h3 class="text-xl font-bold text-gray-900 mb-4">Aktualny Plan (Przeciągaj by zmienić kolejność)</h3>
                    <draggable
                        v-model="selectedPlan"
                        item-key="id"
                        class="flex flex-wrap gap-4 min-h-[120px] p-6 bg-green-50 border-4 border-dashed border-green-300 rounded-3xl mb-8 items-center"
                        ghost-class="opacity-30"
                    >
                        <template #item="{ element, index }">
                            <div class="flex items-center gap-4">
                                <div class="bg-white border-4 border-green-500 rounded-2xl p-4 w-32 h-32 flex flex-col items-center justify-center shadow-md cursor-move relative group">
                                    <button @click="selectedPlan.splice(index, 1)" class="absolute -top-3 -right-3 bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity font-bold">×</button>
                                    <img v-if="element.image_path" :src="element.image_path" class="w-16 h-16 object-contain mb-2 rounded" />
                                    <span class="text-sm font-bold text-gray-900 text-center truncate w-full">{{ element.name }}</span>
                                </div>
                                <div v-if="index < selectedPlan.length - 1" class="text-4xl text-green-500 font-black">
                                    →
                                </div>
                            </div>
                        </template>
                    </draggable>

                    <h3 class="text-xl font-bold text-gray-900 mb-4 pt-6 border-t border-gray-200">Dostępne słowa z tablicy dziecka</h3>
                    <div class="flex flex-wrap gap-4">
                        <div v-for="pic in availableOptions" :key="pic.id" @click="selectedPlan.push(pic)" class="bg-gray-100 border-2 border-gray-300 rounded-xl p-2 w-24 h-24 flex flex-col items-center justify-center shadow-sm cursor-pointer hover:border-blue-500 hover:bg-white transition-all">
                            <img v-if="pic.image_path" :src="pic.image_path" class="w-10 h-10 object-contain mb-1 rounded" />
                            <span class="text-xs font-bold text-gray-900 text-center truncate w-full">{{ pic.name }}</span>
                        </div>
                    </div>

                    <button @click="saveSchedule" class="mt-8 bg-green-600 text-white font-bold py-4 px-8 rounded-2xl hover:bg-green-700 transition w-full shadow-lg shadow-green-200 text-lg">
                        💾 Zapisz Plan Dnia
                    </button>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
