<div>
    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Documents</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                <div class="mt-2 text-xs text-gray-500">{{ $stats['recent'] }} updated this week</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Archived</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['archived'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Shared</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['shared'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Team Members</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $team->users()->count() }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <input 
                        type="text" 
                        placeholder="Search documents..."
                        wire:model.live="search"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                </div>

                <div class="flex gap-2">
                    <select wire:model.live="sort" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="recent">Recent</option>
                        <option value="oldest">Oldest</option>
                        <option value="updated">Recently Updated</option>
                        <option value="title">Title (A-Z)</option>
                    </select>

                    <select wire:model.live="status" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="all">All</option>
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($documents as $document)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                    <a href="{{ route('documents.edit', $document) }}" class="block">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $document->title }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    By {{ $document->user->name }} • Updated {{ $document->updated_at->diffForHumans() }}
                                </p>
                                @if ($document->status)
                                    <span class="mt-2 inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                        {{ ucfirst($document->status) }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-right">
                                @if ($document->shares()->count())
                                    <span class="text-sm text-gray-500">{{ $document->shares()->count() }} shares</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <p>No documents found</p>
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $documents->links() }}
        </div>
    </div>
</div>
