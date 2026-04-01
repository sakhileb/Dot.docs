<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-7 gap-3">
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Comments</p>
            <p class="text-2xl font-semibold text-slate-800 dark:text-sky-50">{{ $summary['total_comments'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Open</p>
            <p class="text-2xl font-semibold text-amber-600">{{ $summary['open_comments'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Resolved</p>
            <p class="text-2xl font-semibold text-emerald-600">{{ $summary['resolved_comments'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Pending Reviews</p>
            <p class="text-2xl font-semibold text-slate-800 dark:text-sky-50">{{ $summary['pending_reviews'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Approved</p>
            <p class="text-2xl font-semibold text-emerald-600">{{ $summary['approved_reviews'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Rejected</p>
            <p class="text-2xl font-semibold text-rose-600">{{ $summary['rejected_reviews'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Mentions</p>
            <p class="text-2xl font-semibold text-slate-800 dark:text-sky-50">{{ $summary['mention_notifications'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 app-card rounded-[2rem] p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">Comment Threads</h3>
                <div class="flex items-center gap-2">
                    <button wire:click="exportComments('markdown')" class="app-pill-button text-xs">Export .md</button>
                    <button wire:click="exportComments('csv')" class="app-pill-button text-xs">Export .csv</button>
                </div>
            </div>

            <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-3 bg-sky-50/40 dark:bg-white/5">
                <x-label for="commentBody" value="New Comment (use @Name for mentions)" />
                <textarea id="commentBody" wire:model="commentBody" rows="3" class="auth-input mt-1 w-full" @disabled(! in_array($accessPermission, ['comment', 'edit'], true))></textarea>
                <x-input-error for="commentBody" class="mt-1" />
                <div class="mt-2 flex justify-end">
                    <button wire:click="addComment" class="rounded-full bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold px-5 py-2 tracking-wide transition" @disabled(! in_array($accessPermission, ['comment', 'edit'], true))>Post Comment</button>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($threads as $thread)
                    <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 {{ $thread->is_resolved ? 'bg-emerald-50/60 dark:bg-emerald-500/8' : 'bg-white/70 dark:bg-white/4' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-sky-50">{{ $thread->user->name ?? 'Unknown User' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $thread->created_at->diffForHumans() }}</p>
                            </div>
                            <button wire:click="toggleResolved({{ $thread->id }})" class="text-xs px-3 py-1 rounded-full border {{ $thread->is_resolved ? 'border-emerald-500 text-emerald-700' : 'border-amber-500 text-amber-700' }}" @disabled(! in_array($accessPermission, ['comment', 'edit'], true))>
                                {{ $thread->is_resolved ? 'Resolved' : 'Mark Resolved' }}
                            </button>
                        </div>

                        <p class="mt-2 text-sm text-slate-700 dark:text-slate-200 whitespace-pre-wrap">{{ $thread->body }}</p>

                        @if ($thread->replies->isNotEmpty())
                            <div class="mt-3 space-y-2">
                                @foreach ($thread->replies as $reply)
                                    <div class="ml-4 pl-3 border-l-2 border-sky-200 dark:border-sky-500/30">
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $reply->user->name ?? 'Unknown User' }} <span class="font-normal text-slate-500">{{ $reply->created_at->diffForHumans() }}</span></p>
                                        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $reply->body }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-3">
                            <textarea wire:model="replyBodies.{{ $thread->id }}" rows="2" class="auth-input w-full" placeholder="Reply to thread..." @disabled(! in_array($accessPermission, ['comment', 'edit'], true))></textarea>
                            <x-input-error for="replyBodies.{{ $thread->id }}" class="mt-1" />
                            <div class="mt-2 flex justify-end">
                                <x-secondary-button wire:click="replyToComment({{ $thread->id }})" @disabled(! in_array($accessPermission, ['comment', 'edit'], true))>Reply</x-secondary-button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500 dark:text-slate-400">No comment threads yet.</div>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="app-card rounded-[2rem] p-5 space-y-3">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">Request Review</h3>

                <div>
                    <x-label for="reviewerUserId" value="Reviewer" />
                    <select id="reviewerUserId" wire:model="reviewerUserId" class="auth-input mt-1 w-full">
                        <option value="">Choose reviewer</option>
                        @foreach ($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="reviewerUserId" class="mt-1" />
                </div>

                <div>
                    <x-label for="reviewType" value="Review Type" />
                    <select id="reviewType" wire:model="reviewType" class="auth-input mt-1 w-full">
                        <option value="general">General</option>
                        <option value="legal">Legal</option>
                        <option value="technical">Technical</option>
                        <option value="editorial">Editorial</option>
                    </select>
                    <x-input-error for="reviewType" class="mt-1" />
                </div>

                <div>
                    <x-label for="reviewRequestNote" value="Request Note" />
                    <textarea id="reviewRequestNote" wire:model="reviewRequestNote" rows="3" class="auth-input mt-1 w-full"></textarea>
                    <x-input-error for="reviewRequestNote" class="mt-1" />
                </div>

                <div class="flex justify-end">
                    <button wire:click="requestReview" class="rounded-full bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold px-5 py-2 tracking-wide transition" @disabled(! in_array($accessPermission, ['comment', 'edit'], true))>Request</button>
                </div>
            </div>

            <div class="app-card rounded-[2rem] p-5 space-y-3">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">Review Decisions</h3>

                <div class="space-y-3 max-h-[480px] overflow-y-auto pr-1">
                    @forelse ($reviews as $review)
                        <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-800 dark:text-sky-50 uppercase">{{ $review->review_type }}</p>
                                <span class="text-xs px-2 py-1 rounded-full {{ $review->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($review->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($review->status) }}</span>
                            </div>
                            <p class="text-xs mt-1 text-slate-500 dark:text-slate-400">Requested by {{ $review->requestedBy->name ?? 'Unknown' }} for {{ $review->reviewer->name ?? 'Unknown' }}</p>
                            @if ($review->request_note)
                                <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">{{ $review->request_note }}</p>
                            @endif

                            @if ((int) $review->reviewer_user_id === (int) auth()->id() && $review->status === 'pending')
                                <textarea wire:model="reviewDecisionNotes.{{ $review->id }}" rows="2" class="auth-input mt-2 w-full" placeholder="Decision note (optional)"></textarea>
                                <div class="mt-2 flex gap-2 justify-end">
                                    <button wire:click="decideReview({{ $review->id }}, 'rejected')" class="px-3 py-1 text-xs rounded-full bg-rose-600 text-white">Reject</button>
                                    <button wire:click="decideReview({{ $review->id }}, 'approved')" class="px-3 py-1 text-xs rounded-full bg-emerald-600 text-white">Approve</button>
                                </div>
                            @elseif ($review->decision_note)
                                <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">Decision: {{ $review->decision_note }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">No review requests yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
