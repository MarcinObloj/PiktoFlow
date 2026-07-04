import { ref, onMounted } from 'vue';

export function useTTS() {
    const voices = ref([]);
    const selectedVoice = ref(null);
    const rate = ref(0.9);
    const pitch = ref(1.0);
    const volume = ref(1.0);

    const loadVoices = () => {
        const availableVoices = window.speechSynthesis.getVoices();

        // Wyciągamy tylko polskie głosy (lub zostawiamy wszystkie, jeśli brak polskich)
        let filtered = availableVoices.filter(v => v.lang.startsWith('pl'));
        if (filtered.length === 0) {
            filtered = availableVoices;
        }

        voices.value = filtered.map(v => {
            let friendlyName = v.name;
            if (friendlyName.includes('Google polski')) friendlyName = 'Polski (Google)';
            else if (friendlyName.includes('Paulina')) friendlyName = 'Paulina (Microsoft)';
            else if (friendlyName.includes('Adam')) friendlyName = 'Adam (Microsoft)';
            else if (friendlyName.includes('Zira')) friendlyName = 'Zira (Microsoft - Angielski)';
            else if (friendlyName.includes('David')) friendlyName = 'David (Microsoft - Angielski)';
            else if (friendlyName.includes('Mark')) friendlyName = 'Mark (Microsoft - Angielski)';
            else {
                // Skracamy przydługie nazwy usuwając wtrącenia o języku
                friendlyName = friendlyName.replace(/ - [a-zA-Z\s]+ \([a-zA-Z-]+\)/, '').trim();
            }

            return {
                original: v,
                voiceURI: v.voiceURI,
                name: v.name,
                friendlyName: friendlyName
            };
        });

        if (!selectedVoice.value && voices.value.length > 0) {
            selectedVoice.value = voices.value[0];
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
        
        if (options.voice) {
            // voices.value is our mapped array, we need the original voice
            const selectedVoiceObj = voices.value.find(v => v.name === options.voice);
            if (selectedVoiceObj) {
                utterance.voice = selectedVoiceObj.original;
            } else {
                // Fallback to native find
                const nativeVoices = window.speechSynthesis.getVoices();
                const fallbackVoice = nativeVoices.find(v => v.name === options.voice);
                if (fallbackVoice) utterance.voice = fallbackVoice;
            }
        } else {
            const nativeVoices = window.speechSynthesis.getVoices();
            const plVoice = nativeVoices.find(v => v.lang.includes('pl'));
            if (plVoice) utterance.voice = plVoice;
        }

        utterance.rate = parseFloat(options.rate) || 1;
        utterance.pitch = parseFloat(options.pitch) || 1;
        utterance.volume = parseFloat(options.volume) || 1;

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
