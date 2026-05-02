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
        if (!('speechSynthesis' in window)) return;

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.voice = options.voice || selectedVoice.value;
        utterance.rate = options.rate || rate.value;
        utterance.pitch = options.pitch || pitch.value;
        utterance.volume = options.volume || volume.value;
        utterance.lang = utterance.voice?.lang || 'pl-PL';

        if (options.onEnd) {
            utterance.onend = options.onEnd;
        }

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
