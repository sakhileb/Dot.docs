<div class="editor-container bg-white dark:bg-gray-900 min-h-screen">
    <!-- Editor Toolbar - Touch Friendly -->
    <div class="editor-toolbar sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-3 md:p-4">
        <div class="flex flex-wrap gap-2 md:gap-3">
            <!-- Text Formatting -->
            <div class="flex gap-1 md:gap-2 border-r border-gray-200 dark:border-gray-700 pr-2 md:pr-3">
                <button wire:click="$dispatch('format', ['bold'])" 
                    class="touch-target p-2.5 md:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                    title="Bold (Ctrl+B)">
                    <svg class="w-5 h-5 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6V4zm0 10h9a5 5 0 0 1 0 10H6v-10z"/></svg>
                </button>
                <button wire:click="$dispatch('format', ['italic'])" 
                    class="touch-target p-2.5 md:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                    title="Italic (Ctrl+I)">
                    <svg class="w-5 h-5 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4h-8z"/></svg>
                </button>
                <button wire:click="$dispatch('format', ['underline'])" 
                    class="touch-target p-2.5 md:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                    title="Underline (Ctrl+U)">
                    <svg class="w-5 h-5 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 3v8a6 6 0 006 6 6 6 0 006-6V3h-3v8a3 3 0 01-6 0V3H6zm12 18H6v-3h12v3z"/></svg>
                </button>
            </div>

            <!-- Headings -->
            <div class="flex gap-1 md:gap-2 border-r border-gray-200 dark:border-gray-700 pr-2 md:pr-3">
                <select wire:change="$dispatch('format', ['heading', $event.target.value])"
                    class="touch-target px-2 py-2.5 md:py-2 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                    <option value="">Text</option>
                    <option value="h1">H1</option>
                    <option value="h2">H2</option>
                    <option value="h3">H3</option>
                </select>
            </div>

            <!-- Colors -->
            <div class="flex gap-1 md:gap-2 border-r border-gray-200 dark:border-gray-700 pr-2 md:pr-3">
                <input type="color" wire:change="$dispatch('format', ['color', $event.target.value])"
                    class="touch-target w-9 h-9 md:w-8 md:h-8 rounded cursor-pointer"
                    title="Text color">
                <input type="color" wire:change="$dispatch('format', ['backgroundColor', $event.target.value])"
                    class="touch-target w-9 h-9 md:w-8 md:h-8 rounded cursor-pointer"
                    title="Background color">
            </div>

            <!-- Lists -->
            <div class="flex gap-1 md:gap-2">
                <button wire:click="$dispatch('format', ['list', 'bullet'])" 
                    class="touch-target p-2.5 md:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                    title="Bullet List">
                    <svg class="w-5 h-5 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3 5h18v2H3V5zm0 6h18v2H3v-2zm0 6h18v2H3v-2z"/></svg>
                </button>
                <button wire:click="$dispatch('format', ['list', 'ordered'])" 
                    class="touch-target p-2.5 md:p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                    title="Numbered List">
                    <svg class="w-5 h-5 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-4h1V9.5H2v1h1v3.5zm-1-6h2V6.5H2v1h1v3.5H2v1h2V7z"/></svg>
                </button>
            </div>
        </div>

        <!-- Font Size for Mobile -->
        <div class="mt-2 md:hidden flex items-center gap-2">
            <button wire:click="decreaseFontSize" class="p-2 bg-gray-100 dark:bg-gray-700 rounded">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13H5v-2h14v2z"/></svg>
            </button>
            <span class="text-sm text-gray-600 dark:text-gray-400 flex-1 text-center">Size: <span wire:model="fontSize">{{ $fontSize }}</span>px</span>
            <button wire:click="increaseFontSize" class="p-2 bg-gray-100 dark:bg-gray-700 rounded">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            </button>
        </div>
    </div>

    <!-- Document Title -->
    @if($editMode)
        <input type="text" 
            wire:change="updateTitle($event.target.value)"
            wire:model.live="title"
            class="w-full px-4 md:px-6 py-3 text-2xl md:text-3xl font-bold border-0 focus:ring-0 bg-white dark:bg-gray-900 dark:text-white"
            placeholder="Document title">
    @else
        <h1 class="px-4 md:px-6 py-3 text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
    @endif

    <!-- Editor Info -->
    <div class="px-4 md:px-6 py-2 text-sm text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap justify-between gap-2">
            <div>By {{ $document->user->name }}</div>
            <div>Modified {{ $document->updated_at->diffForHumans() }}</div>
            <div class="md:hidden">
                {{ \App\Services\ImageOptimizationService::formatFileSize(strlen($content)) }}
            </div>
        </div>

        <!-- User Presence -->
        @if(count($userPresence) > 0)
            <div class="mt-2 text-xs">
                <strong>Editing:</strong> {{ implode(', ', $userPresence) }}
            </div>
        @endif
    </div>

    <!-- Editor Content -->
    <div class="editor-content px-4 md:px-6 py-4 max-w-4xl mx-auto" 
        wire:ignore>
        <div id="editor" class="prose dark:prose-invert max-w-none" @style("font-size: {$fontSize}px;")></div>
    </div>

    <!-- Loading State -->
    <div wire:loading.flex class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>
    </div>
</div>

<!-- Quill Editor Script -->
<script>
    document.addEventListener('livewire:initialized', () => {
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: false,
                imageResize: {},
                table: true,
            },
            placeholder: 'Start typing...',
        });

        quill.on('text-change', () => {
            const content = quill.getLength() > 1 ? quill.root.innerHTML : '';
            window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('updateContent', content);
        });

        quill.on('selection-change', (range) => {
            if (range) {
                window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('updateCursorPosition', range.index);
            }
        });

        Livewire.on('format', (data) => {
            const [format, value] = data;
            quill.format(format, value);
        });

        Livewire.on('content-updated', () => {
            const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
            quill.root.innerHTML = component.get('content');
        });
    });
</script>
