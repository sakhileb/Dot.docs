<div class="space-y-4">
    @forelse ($groupedActivities as $date => $activities)
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
            </h3>

            <div class="space-y-2">
                @foreach ($activities as $activity)
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex-shrink-0 mt-1">
                            <img 
                                src="{{ $activity->user->profile_photo_url }}" 
                                alt="{{ $activity->user->name }}"
                                class="h-8 w-8 rounded-full"
                            />
                        </div>

                        <div class="flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">
                                <span class="font-medium">{{ $activity->user->name }}</span>
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ match($activity->type) {
                                        'edit' => 'edited',
                                        'comment' => 'commented on',
                                        'share' => 'shared',
                                        'review_requested' => 'requested review for',
                                        'review_approved' => 'approved review for',
                                        'review_rejected' => 'rejected review for',
                                        default => $activity->type,
                                    } }}
                                </span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <a href="{{ route('documents.edit', $activity->document) }}" class="hover:underline">
                                    {{ $activity->document->title }}
                                </a>
                            </p>
                            @if ($activity->description)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $activity->description }}</p>
                            @endif
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>No activity yet</p>
        </div>
    @endforelse

    @if (count($groupedActivities) >= 20)
        <button 
            wire:click="loadMore"
            class="w-full py-2 text-center text-sm text-blue-600 dark:text-blue-400 hover:underline"
        >
            Load more activity
        </button>
    @endif
</div>
