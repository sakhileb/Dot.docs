<div class="py-8 max-w-2xl mx-auto px-4">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('documents.edit', $document->uuid) }}" class="text-gray-400 hover:text-gray-600 text-sm">← Back to editor</a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Share Document</h1>
    </div>

    {{-- Public Link --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Public Link</h2>
                <p class="text-xs text-gray-500 mt-0.5">Anyone with the link can view this document</p>
            </div>
            <button wire:click="togglePublicLink"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $document->is_public ? 'bg-indigo-600' : 'bg-gray-300' }}">
                <span class="inline-block h-4 w-4 rounded-full bg-white shadow transform transition {{ $document->is_public ? 'translate-x-6' : 'translate-x-1' }}"></span>
            </button>
        </div>

        @if($document->is_public && $publicLink)
            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700 rounded-lg px-3 py-2">
                <input type="text" value="{{ $publicLink }}" readonly
                       class="flex-1 bg-transparent text-sm text-gray-700 dark:text-gray-200 border-none focus:ring-0 p-0" />
                <button onclick="navigator.clipboard.writeText('{{ $publicLink }}')"
                        class="text-xs text-indigo-600 hover:underline whitespace-nowrap">Copy</button>
            </div>
        @endif
    </div>

    {{-- Invite Collaborators --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Invite People</h2>

        <form wire:submit="invite" class="flex gap-2">
            <input wire:model="inviteEmail" type="email" placeholder="Email address"
                   class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500" />
            <select wire:model="inviteRole"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="viewer">Viewer</option>
                <option value="editor">Editor</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition whitespace-nowrap">
                Invite
            </button>
        </form>
        @error('inviteEmail') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
    </div>

    {{-- Current Collaborators --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">People with access</h2>

        <div class="space-y-3">
            {{-- Owner --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($document->owner->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $document->owner->name }}</p>
                        <p class="text-xs text-gray-400">{{ $document->owner->email }}</p>
                    </div>
                </div>
                <span class="text-xs font-medium text-indigo-600 bg-indigo-50 dark:bg-indigo-900 px-2 py-0.5 rounded-full">Owner</span>
            </div>

            {{-- Collaborators --}}
            @foreach($document->collaborators as $collab)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($collab->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $collab->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $collab->user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500 capitalize">{{ $collab->role }}</span>
                        <button wire:click="removeCollaborator({{ $collab->id }})"
                                class="text-xs text-red-500 hover:text-red-700 transition">Remove</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
