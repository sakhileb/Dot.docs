<div class="flex flex-col h-full bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700">
    @if($pendingSuggestionId)
        <div wire:poll.2s="pollSuggestionStatus" class="px-4 pt-3">
            <div class="rounded-lg border border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 p-3">
                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="font-semibold text-indigo-800 dark:text-indigo-300">Queued AI Task</span>
                    <span class="text-indigo-700 dark:text-indigo-300">{{ $pendingProgress }}%</span>
                </div>
                <progress class="w-full h-2 rounded overflow-hidden" value="{{ $pendingProgress }}" max="100"></progress>
                <p class="mt-2 text-xs text-indigo-700 dark:text-indigo-300">Status: {{ ucfirst($pendingStatus ?? 'pending') }}</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="border-b border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-wand-magic-sparkles text-indigo-600"></i>AI Assistant
            </h3>
            <button wire:click="clearChat" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="Clear chat">
                <i class="fas fa-trash text-xs"></i>
            </button>
        </div>
        @if($selectedText)
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                Selected: {{ Str::limit($selectedText, 40) }}
            </p>
        @else
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Select text to get started
            </p>
        @endif
    </div>

    <!-- Chat History (scrollable) -->
    <div class="flex-1 overflow-y-auto p-4 space-y-3">
        @forelse($chat as $message)
            <div class="flex gap-2 text-xs">
                <div class="flex-shrink-0 w-6 h-6 rounded-full {{ $message['role'] === 'user' ? 'bg-blue-100 dark:bg-blue-900' : 'bg-indigo-100 dark:bg-indigo-900' }} flex items-center justify-center">
                    <i class="fas {{ $message['role'] === 'user' ? 'fa-user' : 'fa-sparkles' }} {{ $message['role'] === 'user' ? 'text-blue-600 dark:text-blue-400' : 'text-indigo-600 dark:text-indigo-400' }} text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-gray-700 dark:text-gray-300 break-words">{{ Str::limit($message['message'], 150) }}</p>
                    @if($message['accepted'])
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            <i class="fas fa-check"></i> Accepted
                        </p>
                    @endif
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $message['timestamp'] }}</p>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 dark:text-gray-400 text-xs py-8">
                <i class="fas fa-comments text-2xl mb-2"></i>
                <p>No messages yet</p>
            </div>
        @endforelse
    </div>

    <!-- AI Response Display -->
    @if($aiResponse)
        <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-indigo-50 dark:bg-indigo-900/20">
            <p class="text-sm text-gray-900 dark:text-gray-100 mb-3 p-2 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                {{ $aiResponse }}
            </p>
            <div class="flex gap-2">
                <button wire:click="acceptSuggestion" class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded transition">
                    <i class="fas fa-check mr-1"></i>Accept
                </button>
                <button wire:click="rejectSuggestion" class="flex-1 px-3 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-900 dark:text-white text-xs font-medium rounded transition">
                    <i class="fas fa-times mr-1"></i>Reject
                </button>
            </div>
        </div>
    @endif

    <!-- AI Tools -->
    <div class="border-t border-gray-200 dark:border-gray-700 p-3 space-y-2 max-h-[400px] overflow-y-auto">
        <!-- Writing Tools Title -->
        <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 px-2 py-1 uppercase tracking-wider">
            Writing Tools
        </div>

        <!-- Completion -->
        <button wire:click="completeText" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed transition" title="Continue writing">
            <i class="fas fa-pen-fancy mr-2"></i>Complete
            @if($isLoading && $currentOperation === 'completion')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <!-- Paraphrase -->
        <button wire:click="paraphrase" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed transition" title="Rephrase with different words">
            <i class="fas fa-arrows-rotate mr-2"></i>Paraphrase
            @if($isLoading && $currentOperation === 'paraphrase')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <!-- Tone Adjustment -->
        <div class="space-y-1">
            <button wire:click="adjustTone" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed transition" title="Change writing tone">
                <i class="fas fa-face-smile mr-2"></i>Change Tone
                @if($isLoading && $currentOperation === 'tone_adjustment')
                    <i class="fas fa-spinner fa-spin float-right"></i>
                @endif
            </button>
            <select wire:model.live="toneSelection" class="w-full px-2 py-1 rounded text-xs border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white bg-white">
                <option value="professional">Professional</option>
                <option value="casual">Casual</option>
                <option value="friendly">Friendly</option>
                <option value="formal">Formal</option>
                <option value="energetic">Energetic</option>
            </select>
        </div>

        <!-- Grammar Check -->
        <button wire:click="checkGrammar" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed transition" title="Check for errors">
            <i class="fas fa-spell-check mr-2"></i>Check Grammar
            @if($isLoading && $currentOperation === 'grammar_check')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <!-- Summarize -->
        <button wire:click="summarize" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed transition" title="Condense to key points">
            <i class="fas fa-compress mr-2"></i>Summarize
            @if($isLoading && $currentOperation === 'summarization')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <!-- Expand -->
        <button wire:click="expandText" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed transition" title="Add more details">
            <i class="fas fa-expand mr-2"></i>Expand
            @if($isLoading && $currentOperation === 'expansion')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <!-- Shorten -->
        <button wire:click="shortenText" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed transition" title="Make more concise">
            <i class="fas fa-compress-alt mr-2"></i>Shorten
            @if($isLoading && $currentOperation === 'shortening')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <!-- Readability -->
        <button wire:click="analyzeReadability" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed transition" title="Analyze ease of reading">
            <i class="fas fa-chart-line mr-2"></i>Readability
            @if($isLoading && $currentOperation === 'readability_analysis')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 px-2 pt-3 uppercase tracking-wider border-t border-gray-200 dark:border-gray-700">
            Formatting & Enhancement
        </div>

        <button wire:click="autoFormatRawText" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-violet-50 hover:bg-violet-100 dark:bg-violet-900/20 dark:hover:bg-violet-900/30 text-violet-800 dark:text-violet-200 disabled:opacity-50 disabled:cursor-not-allowed transition" title="Auto-format raw text">
            <i class="fas fa-wand-magic mr-2"></i>Auto Format
            @if($isLoading && $currentOperation === 'auto_formatting')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <button wire:click="improveContent" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-violet-50 hover:bg-violet-100 dark:bg-violet-900/20 dark:hover:bg-violet-900/30 text-violet-800 dark:text-violet-200 disabled:opacity-50 disabled:cursor-not-allowed transition" title="Improve clarity and flow">
            <i class="fas fa-lightbulb mr-2"></i>Improve Content
            @if($isLoading && $currentOperation === 'content_improvement')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <button wire:click="optimizeStructure" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-violet-50 hover:bg-violet-100 dark:bg-violet-900/20 dark:hover:bg-violet-900/30 text-violet-800 dark:text-violet-200 disabled:opacity-50 disabled:cursor-not-allowed transition" title="Optimize headings and sections">
            <i class="fas fa-sitemap mr-2"></i>Optimize Structure
            @if($isLoading && $currentOperation === 'structure_optimization')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <button wire:click="extractKeyPhrases" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-violet-50 hover:bg-violet-100 dark:bg-violet-900/20 dark:hover:bg-violet-900/30 text-violet-800 dark:text-violet-200 disabled:opacity-50 disabled:cursor-not-allowed transition" title="Extract key phrases">
            <i class="fas fa-key mr-2"></i>Extract Key Phrases
            @if($isLoading && $currentOperation === 'key_phrase_extraction')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <button wire:click="suggestTablesAndCharts" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-violet-50 hover:bg-violet-100 dark:bg-violet-900/20 dark:hover:bg-violet-900/30 text-violet-800 dark:text-violet-200 disabled:opacity-50 disabled:cursor-not-allowed transition" title="Suggest tables and charts">
            <i class="fas fa-table-columns mr-2"></i>Table/Chart Suggestions
            @if($isLoading && $currentOperation === 'table_chart_suggestion')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <div class="space-y-1">
            <button wire:click="generateCitations" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-violet-50 hover:bg-violet-100 dark:bg-violet-900/20 dark:hover:bg-violet-900/30 text-violet-800 dark:text-violet-200 disabled:opacity-50 disabled:cursor-not-allowed transition" title="Generate citations and references">
                <i class="fas fa-book-open mr-2"></i>Generate Citations
                @if($isLoading && $currentOperation === 'citation_generation')
                    <i class="fas fa-spinner fa-spin float-right"></i>
                @endif
            </button>
            <select wire:model.live="citationStyle" class="w-full px-2 py-1 rounded text-xs border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white bg-white">
                <option value="APA">APA</option>
                <option value="MLA">MLA</option>
                <option value="Chicago">Chicago</option>
                <option value="Harvard">Harvard</option>
            </select>
        </div>

        <button wire:click="improveReadability" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-violet-50 hover:bg-violet-100 dark:bg-violet-900/20 dark:hover:bg-violet-900/30 text-violet-800 dark:text-violet-200 disabled:opacity-50 disabled:cursor-not-allowed transition" title="Rewrite for better readability">
            <i class="fas fa-glasses mr-2"></i>Improve Readability
            @if($isLoading && $currentOperation === 'readability_improvement')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>

        <button wire:click="checkPlagiarism" {{ $selectedText ? '' : 'disabled' }} class="w-full text-left px-3 py-2 rounded text-xs font-medium bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/30 text-rose-800 dark:text-rose-200 disabled:opacity-50 disabled:cursor-not-allowed transition" title="Analyze plagiarism risk using AI heuristics">
            <i class="fas fa-fingerprint mr-2"></i>Plagiarism Check
            @if($isLoading && $currentOperation === 'plagiarism_check')
                <i class="fas fa-spinner fa-spin float-right"></i>
            @endif
        </button>
    </div>

    <!-- Status -->
    @if($isLoading)
        <div class="border-t border-gray-200 dark:border-gray-700 p-2 bg-yellow-50 dark:bg-yellow-900/20 text-center">
            <p class="text-xs text-yellow-700 dark:text-yellow-300">
                <i class="fas fa-spinner fa-spin mr-1"></i>Processing...
            </p>
        </div>
    @endif
</div>

