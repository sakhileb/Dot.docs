<div>
    @if (!$editorLoaded)
        <div class="w-full h-96 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center animate-pulse">
            <div class="text-center">
                <div class="inline-block p-4 bg-blue-100 dark:bg-blue-900 rounded-lg mb-4">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-gray-600 dark:text-gray-400">Loading editor...</p>
                <button 
                    wire:click="loadEditor"
                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Load Editor Now
                </button>
            </div>
        </div>
    @else
        <div class="editor-container" x-data x-init="$nextTick(() => $dispatch('editor:loaded'))">
            <livewire:documents.editor :document="$document" />
        </div>
    @endif
</div>
