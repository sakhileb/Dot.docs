<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">Dashboard</h2>
            <a href="{{ route('documents.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#3897D3] hover:bg-[#2a7bbf] text-white text-sm font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Document
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @php
                $recentDocs = \App\Models\Document::where('owner_id', auth()->id())
                    ->orderBy('updated_at', 'desc')
                    ->limit(8)
                    ->get();

                $sharedDocs = \App\Models\Document::whereHas('collaborators', function ($q) {
                    $q->where('user_id', auth()->id());
                })->where('owner_id', '!=', auth()->id())
                    ->orderBy('updated_at', 'desc')
                    ->limit(4)
                    ->get();
            @endphp

            {{-- My Documents --}}
            <section class="mb-10">
                <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">My Documents</h3>

                @if($recentDocs->isEmpty())
                    <div class="rounded-xl border border-white/10 bg-white/5 px-8 py-16 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <p class="text-gray-400 text-sm mb-6">You haven't created any documents yet.</p>
                        <a href="{{ route('documents.index') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#3897D3] hover:bg-[#2a7bbf] text-white text-sm font-semibold transition">
                            Create your first document
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($recentDocs as $doc)
                            <a href="{{ route('documents.edit', $doc->uuid) }}"
                               class="group flex flex-col justify-between rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-sky-400/40 p-5 transition">
                                <div>
                                    <div class="flex items-start justify-between mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#3897D3] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        @if($doc->collaborators->count() > 0)
                                            <span class="inline-flex items-center gap-1 text-xs text-gray-400 bg-white/5 rounded-full px-2 py-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                                </svg>
                                                {{ $doc->collaborators->count() }}
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="font-semibold text-white text-sm leading-snug group-hover:text-sky-300 transition line-clamp-2">
                                        {{ $doc->title ?: 'Untitled Document' }}
                                    </h4>
                                </div>
                                <p class="mt-4 text-xs text-gray-500">
                                    {{ $doc->updated_at->diffForHumans() }}
                                </p>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-4 text-right">
                        <a href="{{ route('documents.index') }}" class="text-sm text-sky-400 hover:text-sky-300 transition">
                            View all documents &rarr;
                        </a>
                    </div>
                @endif
            </section>

            {{-- Shared With Me --}}
            @if($sharedDocs->isNotEmpty())
                <section>
                    <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">Shared With Me</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($sharedDocs as $doc)
                            <a href="{{ route('documents.edit', $doc->uuid) }}"
                               class="group flex flex-col justify-between rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-[#F5C110]/40 p-5 transition">
                                <div>
                                    <div class="flex items-start justify-between mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#F5C110] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <span class="text-xs text-gray-400">{{ $doc->owner->name }}</span>
                                    </div>
                                    <h4 class="font-semibold text-white text-sm leading-snug group-hover:text-yellow-300 transition line-clamp-2">
                                        {{ $doc->title ?: 'Untitled Document' }}
                                    </h4>
                                </div>
                                <p class="mt-4 text-xs text-gray-500">
                                    {{ $doc->updated_at->diffForHumans() }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
