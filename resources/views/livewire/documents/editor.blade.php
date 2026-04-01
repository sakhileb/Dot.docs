<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 min-h-[80vh]">
    <!-- Main Editor -->
    <div class="lg:col-span-3 space-y-6 overflow-y-auto">
        <!-- Header Bar -->
        <div class="app-card rounded-[2rem] border border-white/65 p-5 dark:border-white/8">
            <div class="grid grid-cols-1 lg:grid-cols-6 gap-4 items-end">
                <div class="lg:col-span-3">
                    <x-label for="title" value="Document Title" />
                    <x-input id="title" type="text" class="mt-1 block w-full" wire:model="title" :disabled="$accessPermission !== 'edit'" />
                    <x-input-error for="title" class="mt-1" />
                </div>

                <div>
                    <x-label for="status" value="Status" />
                    <select id="status" wire:model="status" @disabled($accessPermission !== 'edit') class="auth-input mt-1 w-full focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        class="flex-1 justify-center inline-flex items-center px-4 py-2 rounded-full font-semibold text-xs uppercase tracking-[0.14em] text-white bg-sky-600 hover:bg-sky-700 disabled:opacity-50 transition"
                        wire:click="save"
                        @disabled($accessPermission !== 'edit')
                    >
                        Save
                    </button>
                    <button
                        type="button"
                        wire:click="toggleSuggestionMode"
                        @disabled($accessPermission !== 'comment' && $accessPermission !== 'edit')
                        class="px-3 py-2 rounded-full text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        :class="@json($suggestionMode) ? 'bg-sky-600 text-white shadow-[0_10px_22px_rgba(73,163,234,0.25)]' : 'app-pill-button'"
                        title="Toggle suggestion mode"
                    >
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
            </div>

            <div class="mt-3 text-sm text-slate-500 dark:text-sky-50/68">
                Version: v{{ $document->version }} | Word count: {{ $wordCount }} | Suggestion mode: <span class="font-semibold">{{ $suggestionMode ? 'ON' : 'OFF' }}</span>
            </div>

            <div class="mt-1 text-xs text-slate-500 dark:text-sky-50/60">
                Access: <span class="font-semibold uppercase tracking-wide">{{ $accessPermission }}</span>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <button
                    type="button"
                    class="app-pill-button inline-flex items-center px-4 py-2 font-semibold text-xs uppercase tracking-[0.12em] disabled:opacity-50 transition"
                    wire:click="toggleTemplateForm"
                    @disabled($accessPermission !== 'edit')
                >
                    Save As Template
                </button>
                <button
                    type="button"
                    class="app-pill-button inline-flex items-center px-4 py-2 font-semibold text-xs uppercase tracking-[0.12em] disabled:opacity-50 transition"
                    wire:click="toggleMilestoneForm"
                    @disabled($accessPermission !== 'edit')
                >
                    Create Milestone
                </button>
                <a href="{{ route('documents.reviews', $document) }}" class="app-pill-button px-3 py-2 text-sm font-medium">Reviews</a>
                <a href="{{ route('documents.share', $document) }}" class="app-pill-button px-3 py-2 text-sm font-medium">Share</a>
                <a href="{{ route('documents.citations', $document) }}" class="app-pill-button px-3 py-2 text-sm font-medium">Citations</a>
                <a href="{{ route('documents.mail-merge', $document) }}" class="app-pill-button px-3 py-2 text-sm font-medium">Mail Merge</a>
                <a href="{{ route('documents.form-builder', $document) }}" class="app-pill-button px-3 py-2 text-sm font-medium">Form Builder</a>
                <a href="{{ route('documents.versions', $document) }}" class="app-pill-button px-3 py-2 text-sm font-medium">Version History</a>
                <a href="{{ route('templates.index') }}" class="app-pill-button px-3 py-2 text-sm font-medium">Open Template Library</a>
            </div>

            @if ($showMilestoneForm)
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-white/70 dark:bg-sky-500/5">
                    <div>
                        <x-label for="milestoneName" value="Milestone Name" />
                        <x-input id="milestoneName" type="text" class="mt-1 block w-full" wire:model="milestoneName" placeholder="e.g. Draft Ready" />
                        <x-input-error for="milestoneName" class="mt-1" />
                    </div>
                    <div class="md:col-span-2">
                        <x-label for="milestoneNotes" value="Milestone Notes" />
                        <textarea id="milestoneNotes" wire:model="milestoneNotes" rows="2" class="auth-input mt-1 w-full" placeholder="Why this version matters..."></textarea>
                        <x-input-error for="milestoneNotes" class="mt-1" />
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 rounded-full font-semibold text-xs uppercase tracking-[0.14em] text-white bg-sky-600 hover:bg-sky-700 disabled:opacity-50 transition"
                            wire:click="createMilestone"
                            @disabled($accessPermission !== 'edit')
                        >
                            Save Milestone Version
                        </button>
                    </div>
                </div>
            @endif

            @if ($showTemplateForm)
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-white/70 dark:bg-sky-500/5">
                    <div>
                        <x-label for="templateName" value="Template Name" />
                        <x-input id="templateName" type="text" class="mt-1 block w-full" wire:model="templateName" />
                        <x-input-error for="templateName" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="templateCategory" value="Category" />
                        <x-input id="templateCategory" type="text" class="mt-1 block w-full" wire:model="templateCategory" />
                        <x-input-error for="templateCategory" class="mt-1" />
                    </div>
                    <div class="md:col-span-2">
                        <x-label for="templateDescription" value="Description" />
                        <textarea id="templateDescription" wire:model="templateDescription" rows="2" class="auth-input mt-1 w-full"></textarea>
                        <x-input-error for="templateDescription" class="mt-1" />
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                            <input type="checkbox" wire:model="templateIsPublic" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            Share publicly across teams
                        </label>
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 rounded-full font-semibold text-xs uppercase tracking-[0.14em] text-white bg-sky-600 hover:bg-sky-700 disabled:opacity-50 transition"
                            wire:click="saveAsTemplate"
                            @disabled($accessPermission !== 'edit')
                        >
                            Save Template
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Editor -->
        <div class="app-card rounded-[2rem] border border-white/65 p-5 dark:border-white/8" x-data="quillDocumentEditor(@entangle('content').live, @entangle('wordCount'), @entangle('characterCount'), @json($suggestionMode), @json($accessPermission))">
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

                <span class="ql-formats">
                    <button
                        id="voice-dictation-btn"
                        type="button"
                        title="Start voice dictation"
                        class="app-pill-button inline-flex items-center gap-1 px-2 py-1 text-xs"
                    >
                        <i class="fas fa-microphone"></i>
                        <span x-text="isDictating ? 'Stop Dictation' : 'Start Dictation'"></span>
                    </button>
                </span>
            </div>

            <div wire:ignore class="relative">
                <div x-ref="editor" class="min-h-[500px] text-slate-900"></div>
            </div>

            <div class="mt-3 flex justify-between items-center text-sm text-slate-500 dark:text-sky-50/62">
                <span>Word count: <span x-text="wordCount">0</span> | Character count: <span x-text="characterCount">0</span></span>
                <span x-show="supportsDictation" x-cloak>
                    Dictation: <span class="font-semibold" x-text="isDictating ? 'Listening...' : 'Idle'"></span>
                </span>
            </div>

            <x-input-error for="content" class="mt-2" />
        </div>
    </div>

    <!-- AI Assistant & Collaboration Sidebar (1 col) -->
    <div class="lg:col-span-2 flex flex-col gap-4">
        <!-- Tabbed Navigation -->
        <div class="flex gap-0 rounded-[1.25rem] overflow-hidden border border-white/65 dark:border-white/8 app-card" x-data="{ tab: 'collaboration' }">
            <button @click="tab = 'collaboration'" :class="tab === 'collaboration' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="flex-1 px-3 py-2 text-xs font-semibold transition">
                <i class="fas fa-users mr-1"></i>Collab
            </button>
            <button @click="tab = 'ai'" :class="tab === 'ai' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="flex-1 px-3 py-2 text-xs font-semibold transition">
                <i class="fas fa-wand-magic-sparkles mr-1"></i>AI
            </button>
        </div>

        <!-- Collaboration Panel -->
        <div x-show="tab === 'collaboration'" class="flex flex-col gap-4 h-full">
            <!-- Presence Indicators -->
            <div class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 p-4 flex-1">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">
                    <i class="fas fa-users text-sky-600 mr-2"></i>Active Collaborators
                </h3>
                <div class="space-y-2">
                    @forelse($this->getActiveCollaborators() as $presence)
                        <div class="flex items-center gap-2 p-2 rounded-xl bg-sky-50/80 dark:bg-sky-500/10">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-slate-900 dark:text-white truncate">{{ $presence->user->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-sky-50/60">{{ ucfirst($presence->status) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 dark:text-sky-50/60">No other collaborators</p>
                    @endforelse
                </div>
            </div>

            <!-- Activity Log -->
            <div class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 p-4 flex-1 overflow-y-auto">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">
                    <i class="fas fa-history text-sky-600 mr-2"></i>Activity Log
                </h3>
            <div class="space-y-2">
                @forelse($this->getRecentActivity() as $activity)
                    <div class="text-xs border-l-2 border-sky-400 pl-2 py-1">
                        <p class="text-slate-700 dark:text-sky-50/75">
                            <span class="font-medium">{{ $activity->user->name }}</span>
                            {{ $activity->action === 'edit' ? 'edited' : $activity->action }}
                            {{ $activity->action_type }}
                        </p>
                        <p class="text-slate-500 dark:text-sky-50/60">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 dark:text-sky-50/60">No activity yet</p>
                @endforelse
            </div>
            </div>

            <div class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 p-4">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">
                    <i class="fas fa-chart-line text-sky-600 mr-2"></i>Document Analytics
                </h3>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="rounded-xl bg-sky-50/80 dark:bg-sky-500/10 p-3">
                        <p class="text-slate-500 dark:text-sky-50/65">Time Spent</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $analytics['estimated_time_spent_minutes'] ?? 0 }}m</p>
                    </div>
                    <div class="rounded-xl bg-sky-50/80 dark:bg-sky-500/10 p-3">
                        <p class="text-slate-500 dark:text-sky-50/65">Total Edits</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $analytics['total_edits'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-xl bg-sky-50/80 dark:bg-sky-500/10 p-3">
                        <p class="text-slate-500 dark:text-sky-50/65">Comments</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $analytics['total_comments'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-xl bg-sky-50/80 dark:bg-sky-500/10 p-3">
                        <p class="text-slate-500 dark:text-sky-50/65">Contributors</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $analytics['unique_contributors'] ?? 0 }}</p>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500 dark:text-sky-50/60">
                    Last activity:
                    @if (($analytics['recent_activity_at'] ?? null) instanceof \Illuminate\Support\Carbon)
                        {{ $analytics['recent_activity_at']->diffForHumans() }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>

        <!-- AI Assistant Panel -->
        <div x-show="tab === 'ai'" class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 flex-1 overflow-hidden flex flex-col">
            <livewire:documents.ai-assistant :document="$document" />
        </div>
    </div>
</div>


@script
<script>
    Alpine.data('quillDocumentEditor', (boundContent, boundWordCount, boundCharacterCount, suggestionMode, accessPermission) => ({
        content: boundContent,
        wordCount: boundWordCount,
        characterCount: boundCharacterCount,
        suggestionMode: suggestionMode,
        accessPermission: accessPermission,
        quill: null,
        recognition: null,
        isDictating: false,
        supportsDictation: false,
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
                readOnly: this.accessPermission !== 'edit',
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

            if (this.accessPermission !== 'edit') {
                this.quill.enable(false);
            }

            // Track cursor position and text selection
            this.quill.on('selection-change', (range) => {
                if (range) {
                    this.$wire.dispatch('cursor-updated', {
                        position: range.index,
                        selectionStart: range.index,
                        selectionEnd: range.index + range.length,
                    });

                    // Send selected text to AI Assistant
                    if (range.length > 0) {
                        const selectedText = this.quill.getText(range.index, range.length).trim();
                        if (selectedText) {
                            this.$wire.dispatch('text-selected', { text: selectedText });
                        }
                    }
                }
            });

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

            this.setupDictation();

            // Autosave every 60s to create version snapshots while editing.
            setInterval(() => {
                this.$wire.call('autosave');
            }, 60000);
        },
        
        updateWordCount() {
            if (this.quill) {
                const text = this.quill.getText();
                this.wordCount = text.trim().split(/\s+/).filter(word => word.length > 0).length;
                this.characterCount = text.length;
            }
        },

        setupDictation() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.supportsDictation = !!SpeechRecognition;

            const dictationBtn = document.getElementById('voice-dictation-btn');

            if (!this.supportsDictation || !dictationBtn || this.accessPermission !== 'edit') {
                if (dictationBtn && this.accessPermission !== 'edit') {
                    dictationBtn.disabled = true;
                }

                return;
            }

            this.recognition = new SpeechRecognition();
            this.recognition.lang = 'en-US';
            this.recognition.interimResults = true;
            this.recognition.continuous = true;

            this.recognition.onresult = (event) => {
                let transcript = '';

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    transcript += event.results[i][0].transcript;
                }

                if (transcript.trim() !== '') {
                    this.insertDictationText(transcript.trim() + ' ');
                }
            };

            this.recognition.onerror = () => {
                this.isDictating = false;
            };

            this.recognition.onend = () => {
                this.isDictating = false;
            };

            dictationBtn.addEventListener('click', () => {
                this.toggleDictation();
            });
        },

        toggleDictation() {
            if (!this.recognition) {
                return;
            }

            if (this.isDictating) {
                this.recognition.stop();
                this.isDictating = false;

                return;
            }

            this.isDictating = true;
            this.recognition.start();
        },

        insertDictationText(text) {
            if (!this.quill || !text) {
                return;
            }

            const range = this.quill.getSelection(true);
            const index = range ? range.index : this.quill.getLength();
            this.quill.insertText(index, text, 'user');
            this.quill.setSelection(index + text.length, 0, 'silent');
        },
    }));
</script>
@endscript
