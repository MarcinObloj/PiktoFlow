<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateBoardPinForm from './Partials/UpdateBoardPinForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    hasBoardPin: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <Head title="Profil Opiekuna" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">Ustawienia Konta</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <div class="p-6 sm:p-10 bg-white shadow-xl rounded-3xl border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-blue-500"></div>
                    <div class="max-w-xl">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>
                </div>

                <div class="p-6 sm:p-10 bg-white shadow-xl rounded-3xl border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-purple-500"></div>
                    <div class="max-w-xl">
                        <UpdatePasswordForm />
                    </div>
                </div>

                <div class="p-6 sm:p-10 bg-white shadow-xl rounded-3xl border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-amber-500"></div>
                    <div class="max-w-xl">
                        <UpdateBoardPinForm :has-board-pin="hasBoardPin" />
                    </div>
                </div>

                <div class="p-6 sm:p-10 bg-red-50 shadow-xl rounded-3xl border border-red-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-red-500"></div>
                    <div class="max-w-xl">
                        <DeleteUserForm />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
