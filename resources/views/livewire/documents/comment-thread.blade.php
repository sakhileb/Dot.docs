<div class="flex flex-col h-full">

    {{-- Header + filter tabs --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Comments</span>
            @if($totalOpen > 0)
                <span class="text-xs bg-indigo-100 text-indigo-700 rounded-full px-1.5">{{ $totalOpen }}</span>
            @endif
        </div>
        <div class="flex gap-1 text-xs">
            @foreach(['open' => 'Open', 'resolved' => 'Resolved', 'all' => 'All'] as $val => $label)
                <button wire:click="$set('filter', '{{ $val }}')"
                        class="px-2 py-0.5 rounded {{ $filter === $val ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Comment list --}}
    <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($comments as $comment)
            <div class="p-4 {{ $comment->isResolved() ? 'opacity-60' : '' }}" wire:key="comment-{{ $comment->id }}">

                {{-- Selected text anchor --}}
                @if($comment->selection_text)
                    <div class="text-xs bg-amber-50 border-l-2 border-amber-400 text-amber-700 px-2 py-1 mb-2 rounded-r italic truncate">
                        "{{ $comment->selection_text }}"
                    </div>
                @endif

                {{-- Author + time --}}
                <div class="flex items-center gap-2 mb-1.5">
                    <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-white text-[10px] font-bold overflow-hidden">
                        @if($comment->user->profile_photo_path)
                            <img src="{{ $comment->user->profile_photo_url }}" class="w-full h-full object-cover" />
                        @else
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        @endif
                    </div>
                    <span class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $comment->user->name }}</span>
                    <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                    @if($comment->isResolved())
                        <span class="text-[10px] bg-green-100 text-green-700 px-1 rounded ml-auto">Resolved</span>
                    @endif
                </div>

                {{-- Content with @mention highlight --}}
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                    {!! preg_replace('/@(\w+)/', '<span class="text-indigo-600 font-medium">@$1</span>', e($comment->content)) !!}
                </p>

                {{-- Actions --}}
                <div class="flex items-center gap-3 mt-2">
                    <button wire:click="startReply({{ $comment->id }})"
                            class="text-xs text-gray-400 hover:text-indigo-600">Reply</button>

                    @if(!$comment->isResolved())
                        <button wire:click="resolve({{ $comment->id }})"
                                class="text-xs text-gray-400 hover:text-green-600">✓ Resolve</button>
                    @else
                        <button wire:click="reopen({{ $comment->id }})"
                                class="text-xs text-gray-400 hover:text-amber-600">↩ Reopen</button>
                    @endif

                    @if($comment->user_id === auth()->id())
                        <button wire:click="delete({{ $comment->id }})"
                                wire:confirm="Delete this comment?"
                                class="text-xs text-gray-400 hover:text-red-500 ml-auto">Delete</button>
                    @endif
                </div>

                {{-- Replies --}}
                @if($comment->replies->isNotEmpty())
                    <div class="ml-4 mt-3 space-y-3 border-l-2 border-gray-100 dark:border-gray-700 pl-3">
                        @foreach($comment->replies as $reply)
                            <div wire:key="reply-{{ $reply->id }}">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <div class="w-5 h-5 rounded-full bg-purple-500 flex items-center justify-center text-white text-[9px] font-bold overflow-hidden">
                                        @if($reply->user->profile_photo_path)
                                            <img src="{{ $reply->user->profile_photo_url }}" class="w-full h-full object-cover" />
                                        @else
                                            {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $reply->user->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                    {!! preg_replace('/@(\w+)/', '<span class="text-indigo-600 font-medium">@$1</span>', e($reply->content)) !!}
                                </p>
                                @if($reply->user_id === auth()->id())
                                    <button wire:click="delete({{ $reply->id }})"
                                            wire:confirm="Delete this reply?"
                                            class="text-[11px] text-gray-400 hover:text-red-500 mt-0.5">Delete</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Inline reply form --}}
                @if($replyingTo === $comment->id)
                    <div class="mt-3 ml-4" x-data="{ content: @entangle('replyContent') }">
                        <textarea wire:model="replyContent"
                                  rows="2"
                                  placeholder="Reply… use @name to mention"
                                  class="w-full text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500 resize-none"></textarea>
                        <div class="flex gap-2 mt-1.5">
                            <button wire:click="postReply"
                                    class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded">Post</button>
                            <button wire:click="cancelReply"
                                    class="text-xs text-gray-500 hover:underline">Cancel</button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-6 text-center text-sm text-gray-400">
                @if($filter === 'open') No open comments. @elseif($filter === 'resolved') No resolved comments. @else No comments yet. @endif
            </div>
        @endforelse
    </div>

    {{-- New comment form --}}
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <div x-data="mentionInput(@entangle('newComment'), (q) => $wire.searchMentions(q))">
            <textarea x-model="value"
                      @input="handleInput($event)"
                      @keydown.enter.ctrl.prevent="$wire.postComment()"
                      rows="3"
                      placeholder="Add a comment… @mention a collaborator (Ctrl+Enter to post)"
                      class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500 resize-none"></textarea>

            {{-- Mention dropdown --}}
            @if(count($mentionResults) > 0)
                <ul class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg text-sm">
                    @foreach($mentionResults as $u)
                        <li x-on:click="insertMention('{{ $u['name'] }}')"
                            class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                            {{ $u['name'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="flex items-center justify-between mt-2">
            <span class="text-xs text-gray-400">Ctrl+Enter to post</span>
            <button wire:click="postComment"
                    wire:loading.attr="disabled"
                    class="text-xs bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white px-4 py-1.5 rounded">
                Post
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mentionInput', (valueEntangle, onSearch) => ({
        value: valueEntangle,
        handleInput(e) {
            const val = e.target.value;
            const match = val.match(/@(\w*)$/);
            if (match) {
                onSearch(match[1]);
            } else {
                onSearch('');
            }
        },
        insertMention(name) {
            this.value = this.value.replace(/@\w*$/, '@' + name + ' ');
            onSearch('');
        }
    }));
});
</script>
@endpush
