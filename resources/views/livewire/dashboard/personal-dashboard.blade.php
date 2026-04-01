<div>
    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">My Documents</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_documents'] }}</div>
                <div class="mt-2 text-xs text-gray-500">{{ $stats['recent_count'] }} updated this week</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Favorites</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['favorites_count'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Folders</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['folders_count'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Current Team</div>
                <div class="mt-2 text-lg font-bold text-gray-900 dark:text-white">{{ auth()->user()->currentTeam->name }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Documents -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Documents</h2>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recentDocuments as $document)
                        <a href="{{ route('documents.edit', $document) }}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900 dark:text-white">{{ $document->title }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Updated {{ $document->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    @if ($document->shares()->count())
                                        <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                                            Shared
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <p>No recent documents</p>
                            <a href="{{ route('documents.create') }}" class="mt-4 inline-block text-blue-600 dark:text-blue-400 hover:underline">
                                Create your first document
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar: Favorites & Folders -->
        <div class="space-y-6">
            <!-- Favorites -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">⭐ Favorites</h3>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($favoriteDocuments as $document)
                        <a href="{{ route('documents.edit', $document) }}" class="block p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <p class="text-sm text-gray-900 dark:text-white truncate">{{ $document->title }}</p>
                        </a>
                    @empty
                        <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No favorites yet
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Folders -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">📁 Folders</h3>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($folders as $folder)
                        <details class="p-3">
                            <summary class="cursor-pointer text-sm text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded">
                                {{ $folder->name }}
                                <span class="text-xs text-gray-500">{{ $folder->documents()->count() }}</span>
                            </summary>
                            <div class="pl-4 py-2 space-y-1">
                                @forelse ($folder->children as $child)
                                    <p class="text-xs text-gray-600 dark:text-gray-400">├ {{ $child->name }}</p>
                                @empty
                                    <p class="text-xs text-gray-500">empty</p>
                                @endforelse
                            </div>
                        </details>
                    @empty
                        <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No folders yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
