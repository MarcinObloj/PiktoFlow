<script setup>
import { ref, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';

const showingNavigationDropdown = ref(false);
const { isDark, toggleTheme } = useTheme();

const closeMenu = () => {
    showingNavigationDropdown.value = false;
    document.body.style.overflow = 'auto';
};

watch(showingNavigationDropdown, (value) => {
    document.body.style.overflow = value ? 'hidden' : 'auto';
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-50 dark:bg-surface-dark font-sans transition-colors duration-300">
            <nav class="bg-white dark:bg-surface-dark-muted border-b border-gray-100 dark:border-gray-700 shadow-sm sticky top-0 z-40 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-20">
                        <div class="flex items-center">
                            <Link :href="route('dashboard')" class="flex items-center gap-2 group">
                                <div class="bg-primary-600 text-white p-2 rounded-xl shadow-md group-hover:bg-primary-700 transition-colors">
                                    <Icon name="puzzle" :size="26" />
                                </div>
                                <span class="text-2xl font-black text-primary-600 dark:text-primary-400 tracking-tight">PiktoFlow</span>
                            </Link>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="text-sm font-bold uppercase tracking-wider">
                                    Główna Tablica
                                </NavLink>
                                <NavLink :href="route('children.index')" :active="route().current('children.*')" class="text-sm font-bold uppercase tracking-wider">
                                    Profile Dzieci
                                </NavLink>
                                <NavLink :href="route('templates.index')" :active="route().current('templates.*')" class="text-sm font-bold uppercase tracking-wider">
                                    Szablony
                                </NavLink>
                                <NavLink :href="route('statistics.index')" :active="route().current('statistics.index')" class="text-sm font-bold uppercase tracking-wider">
                                    Statystyki
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                            <button
                                type="button"
                                @click="toggleTheme"
                                :aria-label="isDark ? 'Włącz tryb jasny' : 'Włącz tryb ciemny'"
                                :title="isDark ? 'Tryb jasny' : 'Tryb ciemny'"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary-500/40"
                            >
                                <Icon :name="isDark ? 'sun' : 'moon'" :size="20" />
                            </button>

                            <div class="ms-1 relative">
                                <Dropdown align="right" width="48" content-classes="py-1 bg-white dark:bg-surface-dark-muted">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm leading-4 font-bold rounded-xl text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                                                <Icon name="user" :size="16" class="me-2 text-primary-600 dark:text-primary-400" />
                                                {{ $page.props.auth.user.name }}
                                                <Icon name="chevron-down" :size="16" class="ms-2 -me-0.5 text-gray-400" />
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div class="p-2">
                                            <DropdownLink :href="route('profile.edit')" class="rounded-lg font-medium"> Profil Opiekuna </DropdownLink>
                                            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                                            <DropdownLink :href="route('logout')" method="post" as="button" class="rounded-lg font-bold text-red-600 hover:bg-red-50">
                                                Wyloguj się
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center gap-1 sm:hidden z-[60]">
                            <button
                                type="button"
                                @click="toggleTheme"
                                :aria-label="isDark ? 'Włącz tryb jasny' : 'Włącz tryb ciemny'"
                                class="inline-flex items-center justify-center p-2 rounded-xl text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                            >
                                <Icon :name="isDark ? 'sun' : 'moon'" :size="22" />
                            </button>
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                <Icon :name="showingNavigationDropdown ? 'x' : 'menu'" :size="24" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-show="showingNavigationDropdown" class="sm:hidden fixed inset-0 z-50 bg-white dark:bg-surface-dark flex flex-col pt-20">
                    <div class="flex-1 px-4 space-y-4 overflow-y-auto">
                        <div class="border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest px-4 mb-2">Nawigacja</p>
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" @click="closeMenu" class="text-xl font-bold py-4">
                                Główna Tablica
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('children.index')" :active="route().current('children.*')" @click="closeMenu" class="text-xl font-bold py-4">
                                Profile Dzieci
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('templates.index')" :active="route().current('templates.*')" @click="closeMenu" class="text-xl font-bold py-4">
                                Szablony
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('statistics.index')" :active="route().current('statistics.*')" @click="closeMenu" class="text-xl font-bold py-4">
                                Statystyki
                            </ResponsiveNavLink>
                        </div>
                        <div class="pt-4">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest px-4 mb-2">Użytkownik</p>
                            <div class="px-4 py-2 mb-4">
                                <div class="font-black text-lg text-gray-800 dark:text-gray-100">{{ $page.props.auth.user.name }}</div>
                                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ $page.props.auth.user.email }}</div>
                            </div>
                            <ResponsiveNavLink :href="route('profile.edit')" @click="closeMenu" class="text-lg font-bold"> Profil </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button" @click="closeMenu" class="text-lg font-black text-red-600">
                                Wyloguj się
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white dark:bg-surface-dark-muted shadow-sm border-b border-gray-100 dark:border-gray-700 transition-colors" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
