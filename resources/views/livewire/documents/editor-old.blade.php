<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 lg:grid-cols-6 gap-4 items-end">
            <div class="lg:col-span-4">
                <x-label for="title" value="Document Title" />
                <x-input id="title" type="text" class="mt-1 block w-full" wire:model="title" />
                <x-input-error for="title" class="mt-1" />
            </div>

            <div>
                <x-label for="status" value="Status" />
                <select id="status" wire:model="status" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>

            <div>
                <x-button class="w-full justify-center" wire:click="save">Save</x-button>
            </div>
        </div>

        <div class="mt-3 text-sm text-gray-500 dark:text-gray-300">
            Version: v{{ $document->version }}
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5" x-data="quillDocumentEditor(@entangle('content').live, @entangle('wordCount'), @entangle('characterCount'))">
        <div x-ref="toolbar" class="mb-3 quill-toolbar">
            <span class="ql-formats">
                <button class="ql-bold" type="button"></button>
                <button class="ql-italic" type="button"></button>
                <button class="ql-underline" type="button"></button>
                <button class="ql-strike" type="button"></button>
            </span>

            <span class="ql-formats">
                <select class="ql-header">
                    <option selected></option>
                    <option value="1"></option>
                    <option value="2"></option>
                    <option value="3"></option>
                    <option value="4"></option>
                    <option value="5"></option>
                    <option value="6"></option>
                </select>
            </span>

            <span class="ql-formats">
                <button class="ql-list" value="ordered" type="button"></button>
                <button class="ql-list" value="bullet" type="button"></button>
                <button class="ql-list" value="check" type="button"></button>
            </span>

            <span class="ql-formats">
                <button class="ql-blockquote" type="button"></button>
                <button class="ql-code-block" type="button"></button>
            </span>

            <span class="ql-formats">
                <button class="ql-table" type="button"><!-- Table icon --></button>
            </span>

            <span class="ql-formats">
                <button id="image-upload-btn" type="button" title="Insert image">
                    <i class="fas fa-image"></i>
                </button>
                <input type="file" id="image-input" accept="image/*" style="display: none;" />
            </span>

            <span class="ql-formats">
                <select class="ql-align"></select>
            </span>

            <span class="ql-formats">
                <select class="ql-indent" value="-1"></select>
                <select class="ql-indent" value="+1"></select>
            </span>

            <span class="ql-formats">
                <select class="ql-font"></select>
                <select class="ql-size"></select>
            </span>

            <span class="ql-formats">
                <select class="ql-color"></select>
                <select class="ql-background"></select>
            </span>

            <span class="ql-formats">
                <button class="ql-link" type="button"></button>
                <button class="ql-clean" type="button"></button>
            </span>

            <span class="ql-formats">
                <button class="ql-undo" type="button">
                    <i class="fas fa-undo"></i>
                </button>
                <button class="ql-redo" type="button">
                    <i class="fas fa-redo"></i>
                </button>
            </span>
        </div>

        <div wire:ignore>
            <div x-ref="editor" class="min-h-[420px] text-gray-900"></div>
        </div>

        <div class="mt-3 flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
            <span>Word count: <span x-text="wordCount">0</span> | Character count: <span x-text="characterCount">0</span></span>
        </div>

        <x-input-error for="content" class="mt-2" />
    </div>

    @script
    <script>
        Alpine.data('quillDocumentEditor', (boundContent, boundWordCount, boundCharacterCount) => ({
            content: boundContent,
            wordCount: boundWordCount,
            characterCount: boundCharacterCount,
            quill: null,
            init() {
                // Import highlight.js for code block syntax highlighting
                const highlightJS = window.hljs || { highlight: (string, language) => string };

                // Register custom formats
                const Inline = window.Quill.import('blots/inline');
                class HighlightBlot extends Inline {
                    static blotName = 'highlight';
                    static tagName = 'mark';
                }
                window.Quill.register(HighlightBlot);

                this.quill = new window.Quill(this.$refs.editor, {
                    theme: 'snow',
                    modules: {
                        toolbar: this.$refs.toolbar,
                        syntax: { highlight: (code) => highlightJS.highlight(code, { language: 'javascript' }).value },
                        history: {
                            delay: 1000,
                            maxStack: 50,
                            userOnly: true,
                        },
                        table: true,
                        imageResize: {},
                    },
                    formats: [
                        'bold', 'italic', 'underline', 'strike',
                        'header', 'list', 'blockquote', 'code-block', 'table',
                        'align', 'indent',
                        'font', 'size', 'color', 'background',
                        'link', 'image', 'video',
                        'highlight'
                    ],
                });

                this.quill.root.innerHTML = this.content ?? '';

                this.quill.on('text-change', () => {
                    const html = this.quill.root.innerHTML;
                    if (this.content !== html) {
                        this.content = html;
                        this.updateWordCount();
                    }
                });

                this.$watch('content', (value) => {
                    const html = value ?? '';
                    if (this.quill && this.quill.root.innerHTML !== html) {
                        this.quill.root.innerHTML = html;
                        this.updateWordCount();
                    }
                });

                // Setup image upload button
                document.getElementById('image-upload-btn')?.addEventListener('click', () => {
                    document.getElementById('image-input').click();
                });

                document.getElementById('image-input')?.addEventListener('change', (e) => {
                    const file = e.target.files?.[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            const range = this.quill.getSelection();
                            if (range) {
                                this.quill.insertEmbed(range.index, 'image', event.target.result);
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                    e.target.value = '';
                });

                // Setup table button
                document.querySelector('.ql-table')?.addEventListener('click', () => {
                    const range = this.quill.getSelection();
                    if (range) {
                        const Table = window.Quill.import('modules/table');
                        new Table().insertTable(3, 3);
                    }
                });

                // Sync Undo/Redo buttons
                document.querySelector('.ql-undo')?.addEventListener('click', () => {
                    this.quill.history.undo();
                });

                document.querySelector('.ql-redo')?.addEventListener('click', () => {
                    this.quill.history.redo();
                });

                this.updateWordCount();
            },
            
            updateWordCount() {
                if (this.quill) {
                    const text = this.quill.getText();
                    this.wordCount = text.trim().split(/\s+/).filter(word => word.length > 0).length;
                    this.characterCount = text.length;
                }
            },
        }));
    </script>
    @endscript
</div>
