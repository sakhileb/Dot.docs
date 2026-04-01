<div class="space-y-6">
    <div>
        <h3 class="text-2xl font-semibold tracking-[-0.03em] text-slate-900 dark:text-white mb-3">Notification Preferences</h3>
        <p class="text-sm text-slate-500 dark:text-sky-50/60 mb-6">Choose how you want to be notified about document activities.</p>
    </div>

    <div class="space-y-6">
        <!-- Document Changes -->
        <div class="settings-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-semibold text-slate-900 dark:text-white">Document Changes</h4>
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">When documents you have access to are updated</p>
                </div>
            </div>
            <div class="space-y-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="document_changes_email" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Email notifications
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="document_changes_browser" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Browser notifications
                </label>
            </div>
        </div>

        <!-- Comments -->
        <div class="settings-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-semibold text-slate-900 dark:text-white">Comments</h4>
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">When someone comments on documents you're involved with</p>
                </div>
            </div>
            <div class="space-y-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="comments_email" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Email notifications
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="comments_browser" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Browser notifications
                </label>
            </div>
        </div>

        <!-- Mentions -->
        <div class="settings-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-semibold text-slate-900 dark:text-white">Mentions</h4>
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">When someone mentions you (@username) in comments</p>
                </div>
            </div>
            <div class="space-y-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="mentions_email" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Email notifications
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="mentions_browser" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Browser notifications
                </label>
            </div>
        </div>

        <!-- Shares -->
        <div class="settings-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-semibold text-slate-900 dark:text-white">Document Shares</h4>
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">When someone shares a document with you</p>
                </div>
            </div>
            <div class="space-y-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="shares_email" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Email notifications
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="shares_browser" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Browser notifications
                </label>
            </div>
        </div>

        <!-- Reviews -->
        <div class="settings-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-semibold text-slate-900 dark:text-white">Reviews & Decisions</h4>
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">When reviews are requested or decisions are made</p>
                </div>
            </div>
            <div class="space-y-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="reviews_email" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Email notifications
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="reviews_browser" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Browser notifications
                </label>
            </div>
        </div>

        <!-- Push Notifications -->
        <div class="settings-panel p-5 bg-sky-50/70 dark:bg-sky-500/12">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-semibold text-slate-900 dark:text-white">Browser Push Notifications</h4>
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">Get instant notifications even when not on the website</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-sky-50/70">
                    <input type="checkbox" wire:model="push_enabled" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                    Enable
                </label>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <x-button wire:click="save">Save Preferences</x-button>
    </div>
</div>
