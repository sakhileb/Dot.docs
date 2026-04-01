<div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Total Shares</p>
            <p class="text-2xl font-semibold text-slate-800 dark:text-sky-50">{{ $analytics['total_shares'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Active</p>
            <p class="text-2xl font-semibold text-emerald-600">{{ $analytics['active_shares'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Views</p>
            <p class="text-2xl font-semibold text-slate-800 dark:text-sky-50">{{ $analytics['total_views'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Edits</p>
            <p class="text-2xl font-semibold text-slate-800 dark:text-sky-50">{{ $analytics['total_edits'] }}</p>
        </div>
        <div class="app-card rounded-[1.25rem] p-3">
            <p class="text-xs text-slate-500 dark:text-slate-400">Public Link Opens</p>
            <p class="text-2xl font-semibold text-slate-800 dark:text-sky-50">{{ $analytics['link_access'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="app-card rounded-[2rem] p-5 space-y-4">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">Share With Team Member</h3>

            <div>
                <x-label for="memberUserId" value="Team Member" />
                <select id="memberUserId" wire:model="memberUserId" class="auth-input mt-1 w-full">
                    <option value="">Select member</option>
                    @foreach ($teamMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                    @endforeach
                </select>
                <x-input-error for="memberUserId" class="mt-1" />
            </div>

            <div>
                <x-label for="memberPermission" value="Permission" />
                <select id="memberPermission" wire:model="memberPermission" class="auth-input mt-1 w-full">
                    <option value="view">View</option>
                    <option value="comment">Comment</option>
                    <option value="edit">Edit</option>
                </select>
                <x-input-error for="memberPermission" class="mt-1" />
            </div>

            <div class="flex justify-end">
                <button wire:click="shareWithMember" class="rounded-full bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold px-5 py-2 tracking-wide transition">Save Member Share</button>
            </div>
        </div>

        <div class="app-card rounded-[2rem] p-5 space-y-4">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">Create Public Link</h3>

            <div>
                <x-label for="publicPermission" value="Permission" />
                <select id="publicPermission" wire:model="publicPermission" class="auth-input mt-1 w-full">
                    <option value="view">View</option>
                    <option value="comment">Comment</option>
                    <option value="edit">Edit</option>
                </select>
                <x-input-error for="publicPermission" class="mt-1" />
            </div>

            <div>
                <x-label for="publicExpiresAt" value="Expires At (optional)" />
                <x-input id="publicExpiresAt" type="datetime-local" wire:model="publicExpiresAt" class="mt-1 block w-full" />
                <x-input-error for="publicExpiresAt" class="mt-1" />
            </div>

            <div>
                <x-label for="publicPassword" value="Password (optional)" />
                <x-input id="publicPassword" type="password" wire:model="publicPassword" class="mt-1 block w-full" placeholder="Minimum 6 characters" />
                <x-input-error for="publicPassword" class="mt-1" />
            </div>

            <div>
                <x-label for="publicDomain" value="Allowed Domain (optional)" />
                <x-input id="publicDomain" type="text" wire:model="publicDomain" class="mt-1 block w-full" placeholder="example.com" />
                <x-input-error for="publicDomain" class="mt-1" />
            </div>

            <div class="flex justify-end">
                <button wire:click="createPublicLink" class="rounded-full bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold px-5 py-2 tracking-wide transition">Create Public Link</button>
            </div>
        </div>
    </div>

    <div class="app-card rounded-[2rem] overflow-hidden">
        <div class="px-5 py-4 border-b border-sky-100 dark:border-white/10">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">Current Shares</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-sky-100 dark:divide-white/8">
                <thead class="bg-sky-50/60 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Target</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Permission</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Security</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Analytics</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sky-100 dark:divide-white/8">
                    @forelse ($shares as $share)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-800 dark:text-sky-50">
                                @if ($share->is_public_link)
                                    <div class="font-medium">Public Link</div>
                                    <a href="{{ route('shares.public', $share->access_token) }}" target="_blank" class="text-xs text-sky-600 hover:underline">Open shared URL</a>
                                @else
                                    <div class="font-medium">{{ $share->sharedWithUser?->name ?? 'User removed' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $share->shared_with_email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300 uppercase">{{ $share->permission }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                @if ($share->password)
                                    <span class="inline-flex px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Password</span>
                                @endif
                                @if ($share->allowed_domain)
                                    <div class="text-xs">Domain: {{ $share->allowed_domain }}</div>
                                @endif
                                @if ($share->expires_at)
                                    <div class="text-xs">Expires: {{ $share->expires_at->toDayDateTimeString() }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300">
                                Views: {{ $share->views_count }}<br>
                                Edits: {{ $share->edits_count }}<br>
                                Link opens: {{ $share->link_access_count }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($share->status === 'active')
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Revoked</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                @if ($share->status === 'active')
                                    <button wire:click="revokeShare({{ $share->id }})" class="text-red-600 hover:underline">Revoke</button>
                                @else
                                    <button wire:click="activateShare({{ $share->id }})" class="text-sky-600 hover:underline">Reactivate</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No shares created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
