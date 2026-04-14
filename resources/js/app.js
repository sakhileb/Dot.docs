import './bootstrap';
import './editor';
import Alpine from 'alpinejs';
import '@hotwired/turbo';
import _ from 'lodash';
import { initOfflineSupport } from './offline';

window.Alpine = Alpine;
window._ = _;

// Register service worker + global online/offline state
initOfflineSupport(
    () => window.dispatchEvent(new CustomEvent('app-online')),
    () => window.dispatchEvent(new CustomEvent('app-offline'))
);

/**
 * Voice Typing component (Web Speech API).
 * Injects recognised speech directly into the active TipTap editor.
 */
window.voiceTyping = function () {
    return {
        supported: false,
        listening: false,
        recognition: null,

        init() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.supported = !!SpeechRecognition;
            if (!this.supported) return;

            this.recognition = new SpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = false;
            this.recognition.lang = document.documentElement.lang || 'en-US';

            this.recognition.onresult = (event) => {
                const transcript = Array.from(event.results)
                    .slice(event.resultIndex)
                    .filter(r => r.isFinal)
                    .map(r => r[0].transcript)
                    .join(' ');

                if (transcript.trim()) {
                    // Find the nearest TipTap editor and insert text
                    const editorEl = document.querySelector('.ProseMirror');
                    if (editorEl) {
                        // Dispatch to the editor Alpine component to insert text
                        window.dispatchEvent(new CustomEvent('voice-transcript', {
                            detail: { text: transcript.trim() }
                        }));
                    }
                }
            };

            this.recognition.onerror = () => { this.listening = false; };
            this.recognition.onend = () => { this.listening = false; };
        },

        toggle() {
            if (!this.supported) return;
            if (this.listening) {
                this.recognition.stop();
                this.listening = false;
            } else {
                this.recognition.start();
                this.listening = true;
            }
        }
    };
};

Alpine.start();
