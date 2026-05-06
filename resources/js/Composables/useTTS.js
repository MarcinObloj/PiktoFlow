import { ref, onMounted } from 'vue';

export function useTTS() {
    const voices = ref([]);
    const selectedVoice = ref(null);
    const rate = ref(0.9);
    const pitch = ref(1.0);
    const volume = ref(1.0);

    const loadVoices = () => {
        const availableVoices = window.speechSynthesis.getVoices();
        voices.value = availableVoices;

        // Default to first Polish voice if available, otherwise first available
        if (!selectedVoice.value && availableVoices.length > 0) {
            const polishVoice = availableVoices.find(v => v.lang.startsWith('pl'));
            selectedVoice.value = polishVoice || availableVoices[0];
        }
    };

    onMounted(() => {
        if ('speechSynthesis' in window) {
            loadVoices();
            if (window.speechSynthesis.onvoiceschanged !== undefined) {
                window.speechSynthesis.onvoiceschanged = loadVoices;
            }
        }
    });

    const speak = (text, options = {}) => {
        if (!window.speechSynthesis) return;

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        const voices = window.speechSynthesis.getVoices();

        if (options.voice) {
            const selectedVoice = voices.find(v => v.name === options.voice);
            if (selectedVoice) {
                utterance.voice = selectedVoice;
            }
        } else {
            const plVoice = voices.find(v => v.lang.includes('pl'));
            if (plVoice) utterance.voice = plVoice;
        }

        utterance.rate = parseFloat(options.rate) || 1;
        utterance.pitch = parseFloat(options.pitch) || 1;
        utterance.volume = parseFloat(options.volume) || 1;

        window.speechSynthesis.speak(utterance);
    };

    const stop = () => {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }
    };

    return {
        voices,
        selectedVoice,
        rate,
        pitch,
        volume,
        speak,
        stop,
        loadVoices
    };
}
