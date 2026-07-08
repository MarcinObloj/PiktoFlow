import { ref } from 'vue';

const STORAGE_KEY = 'piktoflow-theme';
const isDark = ref(false);

const apply = (dark) => {
    isDark.value = dark;
    if (typeof document === 'undefined') return;
    document.documentElement.classList.toggle('dark', dark);
};

/**
 * Applies the saved preference. Defaults to LIGHT so the theme is predictable
 * and never surprises the user based on their OS setting — dark mode is opt-in
 * via the toggle in the top navigation bar.
 */
export function initTheme() {
    if (typeof window === 'undefined') return;
    const stored = localStorage.getItem(STORAGE_KEY);
    apply(stored === 'dark');
}

export function useTheme() {
    const toggleTheme = () => {
        apply(!isDark.value);
        localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light');
    };

    return { isDark, toggleTheme };
}
