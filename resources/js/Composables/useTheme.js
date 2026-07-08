import { ref } from 'vue';

const STORAGE_KEY = 'piktoflow-theme';
const isDark = ref(false);

const apply = (dark) => {
    isDark.value = dark;
    if (typeof document === 'undefined') return;
    document.documentElement.classList.toggle('dark', dark);
};

/**
 * Reads the saved preference (or the OS setting) and applies it.
 * Safe to call early (e.g. from app.js) to avoid a flash of the wrong theme.
 */
export function initTheme() {
    if (typeof window === 'undefined') return;
    const stored = localStorage.getItem(STORAGE_KEY);
    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
    apply(stored ? stored === 'dark' : !!prefersDark);
}

export function useTheme() {
    const toggleTheme = () => {
        apply(!isDark.value);
        localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light');
    };

    return { isDark, toggleTheme };
}
