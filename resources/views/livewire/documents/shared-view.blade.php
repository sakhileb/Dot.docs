<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10 px-4">
    <div class="max-w-4xl mx-auto">
        @if ($accessError)
            <div class="rounded-xl border border-red-200 bg-red-50 p-6 text-red-700">
                <h1 class="text-lg font-semibold">Access Denied</h1>
                <p class="mt-2 text-sm">{{ $accessError }}</p>
            </div>
        @elseif ($requiresSignIn)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-amber-800">
                <h1 class="text-lg font-semibold">Sign In Required</h1>
                <p class="mt-2 text-sm">This shared link is restricted to a specific email domain. Please sign in first.</p>
                <a href="{{ route('login') }}" class="mt-4 inline-flex px-4 py-2 rounded-md bg-amber-600 text-white text-sm">Sign In</a>
            </div>
        @elseif (! $authorized)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Password Protected Share</h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">Enter the password to open this document.</p>

                <div class="mt-4 max-w-sm">
                    <x-input type="password" wire:model="password" class="block w-full" placeholder="Share password" />
                    <x-input-error for="password" class="mt-1" />
                    <div class="mt-3">
                        <x-button wire:click="submitPassword">Unlock Document</x-button>
                    </div>
                </div>
            </div>
        @elseif ($share)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $share->document->title }}</h1>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mt-1">Permission: {{ $share->permission }}</p>
                    </div>
                    @auth
                        @if ($share->permission === 'edit')
                            <a href="{{ route('documents.edit', $share->document) }}" class="inline-flex px-4 py-2 rounded-md bg-indigo-600 text-white text-sm">Open in Editor</a>
                        @endif
                    @endauth
                </div>

                <article class="prose dark:prose-invert max-w-none mt-6">
                    {!! $share->document->content !!}
                </article>
            </div>
        @endif
    </div>
</div>
