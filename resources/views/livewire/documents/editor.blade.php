<div
    x-data="{
        editor: null,
        echo: null,
        saveTimeout: null,
        heartbeatInterval: null,
        isTyping: false,
        typingTimeout: null,
        remoteVersion: @entangle('document.version').live,

        init() {
            this.editor = window.createTipTapEditor({
                element: this.$refs.editorEl,
                content: @js($content),
                uploadUrl: '{{ route('documents.images.store', $document->uuid) }}',
                csrfToken: document.querySelector('meta[name=csrf-token]').content,
                onChange: (html) => {
                    // Show typing indicator
                    this.isTyping = true;
                    clearTimeout(this.typingTimeout);
                    this.typingTimeout = setTimeout(() => { this.isTyping = false; }, 1000);

                    // Debounced autosave
                    clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(() => {
                        @this.saveContent(html);
                    }, 1500);
                }
            });

            this.setupEcho();

            // Heartbeat every 60 seconds to keep presence alive
            this.heartbeatInterval = setInterval(() => {
                @this.heartbeat();
            }, 60000);

            // Notify server when tab/window is closed
            window.addEventListener('beforeunload', () => {
                @this.leaving();
            });
        },

        setupEcho() {
            if (typeof window.Echo === 'undefined') return;

            this.echo = window.Echo.join('document.{{ $document->id }}')
                .here((users) => {
                    // Initial member list from presence channel
                })
                .joining((user) => {
                    console.log(user.name + ' joined');
                })
                .leaving((user) => {
                    console.log(user.name + ' left');
                })
                .listen('.document.updated', (e) => {
                    // Only apply remote updates if from another user
                    if (e.editor.id !== {{ auth()->id() }}) {
                        const currentPos = this.editor.state.selection.anchor;
                        this.editor.commands.setContent(e.content, false);
                        // Try to restore cursor position
                        try { this.editor.commands.setTextSelection(currentPos); } catch(_) {}
                    }
                })
                .listen('.user.joined', (e) => {
                    @this.heartbeat();
                })
                .listen('.user.left', (e) => {
                    @this.heartbeat();
                });
        },

        destroy() {
            clearInterval(this.heartbeatInterval);
            if (this.echo) this.echo.leave();
            if (this.editor) this.editor.destroy();
        }
    }"
    x-init="init()"
    x-destroy="destroy()"
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
                :class="editor?.isActive('bold') ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                class="p-1.5 rounded transition text-sm font-bold">B</button>

        <button @click="editor.chain().focus().toggleItalic().run()" title="Italic"
                :class="editor?.isActive('italic') ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                class="p-1.5 rounded transition text-sm italic">I</button>

        <span class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></span>

        @foreach([1,2,3] as $h)
            <button @click="editor.chain().focus().toggleHeading({ level: {{ $h }} }).run()"
                    :class="editor?.isActive('heading', { level: {{ $h }} }) ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                    class="p-1.5 rounded transition text-xs font-bold">H{{ $h }}</button>
        @endforeach

        <span class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></span>

        <button @click="editor.chain().focus().toggleBulletList().run()"
                :class="editor?.isActive('bulletList') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                class="p-1.5 rounded transition text-sm">• List</button>

        <button @click="editor.chain().focus().toggleOrderedList().run()"
                :class="editor?.isActive('orderedList') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                class="p-1.5 rounded transition text-sm">1. List</button>

        <span class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></span>

        <button @click="editor.chain().focus().toggleBlockquote().run()"
                :class="editor?.isActive('blockquote') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                class="p-1.5 rounded transition text-sm" title="Blockquote">"</button>

        <button @click="editor.chain().focus().toggleCode().run()"
                :class="editor?.isActive('code') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                class="p-1.5 rounded transition text-xs font-mono" title="Inline code">&lt;/&gt;</button>

        <button @click="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()"
                class="p-1.5 rounded transition text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700" title="Insert Table">⊞ Table</button>

        {{-- Image upload --}}
        <label class="p-1.5 rounded transition text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 cursor-pointer" title="Insert Image">
            🖼
            <input type="file" accept="image/*" class="hidden"
                   @change="editor.uploadImage($event.target.files[0]); $event.target.value = ''" />
        </label>

        <span class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></span>

        <button @click="editor.chain().focus().undo().run()" title="Undo"
                class="p-1.5 rounded transition text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">↩</button>
        <button @click="editor.chain().focus().redo().run()" title="Redo"
                class="p-1.5 rounded transition text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">↪</button>

        {{-- Right side: presence + status + nav --}}
        <div class="ml-auto flex items-center gap-3">
            {{-- Active user avatars --}}
            @if(count($activeUsers) > 0)
                <div class="flex items-center -space-x-1.5">
                    @foreach(array_slice($activeUsers, 0, 4) as $member)
                        <div title="{{ $member['name'] }}"
                             class="w-7 h-7 rounded-full border-2 border-white dark:border-gray-800 overflow-hidden bg-indigo-500 flex items-center justify-center text-white text-xs font-bold">
                            @if(!empty($member['avatar']))
                                <img src="{{ $member['avatar'] }}" alt="{{ $member['name'] }}" class="w-full h-full object-cover" />
                            @else
                                {{ strtoupper(substr($member['name'], 0, 1)) }}
                            @endif
                        </div>
                    @endforeach
                    @if(count($activeUsers) > 4)
                        <div class="w-7 h-7 rounded-full border-2 border-white dark:border-gray-800 bg-gray-400 flex items-center justify-center text-white text-xs font-bold">
                            +{{ count($activeUsers) - 4 }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Typing / save indicator --}}
            <div class="flex items-center gap-1 text-xs text-gray-400">
                <span x-show="isTyping" class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></span>
                    editing
                </span>
                <span wire:loading wire:target="saveContent,saveTitle" class="animate-pulse">Saving…</span>
                <span wire:loading.remove wire:target="saveContent,saveTitle" x-show="!isTyping" class="text-green-500">
                    @if($saved) ✓ Saved @endif
                </span>
            </div>

            {{-- Last edited by --}}
            <span class="text-xs text-gray-400 hidden lg:block">
                v{{ $document->version }}
                · edited {{ $document->updated_at->diffForHumans() }}
            </span>

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
                 class="prose prose-lg dark:prose-invert max-w-none min-h-[60vh] focus:outline-none [&_.ProseMirror]:outline-none [&_.ProseMirror-focused]:outline-none"></div>
        </div>
    </div>
</div>

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
