<div class="space-y-4">
    <div class="flex gap-2">
        <input 
            type="text"
            placeholder="Search documents (min 2 characters)..."
            wire:model.live="query"
            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
        
        <select 
            wire:model.live="searchType"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
        >
            <option value="all">All Fields</option>
            <option value="title">Title</option>
            <option value="content">Content</option>
            <option value="comments">Comments</option>
        </select>
    </div>

    @if ($query && strlen($query) >= 2)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            @forelse ($results as $document)
                <a href="{{ route('documents.edit', $document) }}" class="block p-4 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $document->title }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 truncate">
                                {{ Str::limit(strip_tags($document->content), 150) }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500">
                                By {{ $document->user->name }} • Updated {{ $document->updated_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            @if ($document->shares()->count())
                                <span class="inline-block px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                                    Shared ({{ $document->shares()->count() }})
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <p>No documents found matching "{{ $query }}"</p>
                </div>
            @endforelse

            @if ($results->count())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $results->links() }}
                </div>
            @endif
        </div>
    @elseif ($query && strlen($query) < 2)
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg text-sm text-blue-700 dark:text-blue-300">
            Type at least 2 characters to search
        </div>
    @endif
</div>
