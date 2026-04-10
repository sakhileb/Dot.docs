<div
    x-data="{
        editor: null,
        saveTimeout: null,
        init() {
            this.editor = window.createTipTapEditor({
                element: this.$refs.editorEl,
                content: @js($content),
                uploadUrl: '{{ route('documents.images.store', $document->uuid) }}',
                csrfToken: document.querySelector('meta[name=csrf-token]').content,
                onChange: (html) => {
                    clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(() => {
                        @this.saveContent(html);
                    }, 1500);
                }
            });
        }
    }"
    x-init="init()"
    class="flex flex-col h-screen bg-gray-50 dark:bg-gray-900"
>
    {{-- Toolbar --}}
    <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-2 flex items-center gap-1 flex-wrap">
        {{-- Title in toolbar --}}
        <div class="flex-1 min-w-0 mr-4">
            <input wire:model.blur="title"
                   wire:change="saveTitle"
                   type="text"
                   class="w-full text-lg font-semibold bg-transparent border-none focus:ring-0 text-gray-900 dark:text-white truncate p-0"
                   placeholder="Untitled" />
        </div>

        {{-- Format buttons --}}
        <button @click="editor.chain().focus().toggleBold().run()" title="Bold"
                :class="editor?.isActive('bold') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
                class="p-1.5 rounded transition text-sm font-bold">B</button>

        <button @click="editor.chain().focus().toggleItalic().run()" title="Italic"
                :class="editor?.isActive('italic') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
                class="p-1.5 rounded transition text-sm italic">I</button>

        <button @click="editor.chain().focus().toggleUnderline?.().run()" title="Underline"
                class="p-1.5 rounded transition text-sm underline text-gray-600 hover:bg-gray-100">U</button>

        <span class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></span>

        @foreach([1,2,3] as $h)
            <button @click="editor.chain().focus().toggleHeading({ level: {{ $h }} }).run()"
                    :class="editor?.isActive('heading', { level: {{ $h }} }) ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
                    class="p-1.5 rounded transition text-xs font-bold">H{{ $h }}</button>
        @endforeach

        <span class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></span>

        <button @click="editor.chain().focus().toggleBulletList().run()"
                :class="editor?.isActive('bulletList') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
                class="p-1.5 rounded transition text-sm">• List</button>

        <button @click="editor.chain().focus().toggleOrderedList().run()"
                :class="editor?.isActive('orderedList') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
                class="p-1.5 rounded transition text-sm">1. List</button>

        <span class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></span>

        <button @click="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()"
                class="p-1.5 rounded transition text-sm text-gray-600 hover:bg-gray-100" title="Insert Table">⊞ Table</button>

        {{-- Image upload --}}
        <label class="p-1.5 rounded transition text-sm text-gray-600 hover:bg-gray-100 cursor-pointer" title="Insert Image">
            🖼
            <input type="file" accept="image/*" class="hidden"
                   @change="editor.uploadImage($event.target.files[0]); $event.target.value = ''" />
        </label>

        <span class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></span>

        <button @click="editor.chain().focus().undo().run()" title="Undo"
                class="p-1.5 rounded transition text-sm text-gray-600 hover:bg-gray-100">↩</button>
        <button @click="editor.chain().focus().redo().run()" title="Redo"
                class="p-1.5 rounded transition text-sm text-gray-600 hover:bg-gray-100">↪</button>

        {{-- Actions --}}
        <div class="ml-auto flex items-center gap-2">
            <span wire:loading wire:target="saveContent,saveTitle"
                  class="text-xs text-gray-400 animate-pulse">Saving…</span>
            <a href="{{ route('documents.share', $document->uuid) }}"
               class="text-xs text-indigo-600 hover:underline">Share</a>
            <a href="{{ route('documents.settings', $document->uuid) }}"
               class="text-xs text-gray-500 hover:underline">Settings</a>
            <a href="{{ route('documents.index') }}"
               class="text-xs text-gray-500 hover:underline">← All Docs</a>
        </div>
    </div>

    {{-- Editor area --}}
    <div class="flex-1 overflow-auto">
        <div class="max-w-4xl mx-auto py-10 px-6">
            <div x-ref="editorEl"
                 class="prose prose-lg dark:prose-invert max-w-none min-h-[60vh] focus:outline-none"></div>
        </div>
    </div>
</div>
