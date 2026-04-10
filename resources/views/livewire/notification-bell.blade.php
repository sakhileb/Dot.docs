<div x-data="{ open: @entangle('open').live }" class="relative">
    {{-- Bell button --}}
    <button @click="$wire.toggle()"
            class="relative p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition focus:outline-none"
            title="Notifications">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-0.5 leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown panel --}}
    <div x-show="open"
         @click.outside="open = false; $wire.open = false"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllRead"
                        class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    Mark all read
                </button>
            @endif
        </div>

        {{-- Notification list --}}
        <div class="max-h-72 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700">
            @forelse($notifications as $notification)
                <div class="flex items-start gap-3 px-4 py-3 {{ !$notification['read'] ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/40' }} transition">
                    <div class="flex-shrink-0 mt-0.5">
                        @if($notification['type'] === 'mention')
                            <span class="text-base">&#64;</span>
                        @else
                            <span class="text-base">&#x1F4AC;</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        @if($notification['url'])
                            <a href="{{ $notification['url'] }}"
                               wire:click="markRead('{{ $notification['id'] }}')"
                               class="text-xs text-gray-800 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-2">
                                {{ $notification['message'] }}
                            </a>
                        @else
                            <p class="text-xs text-gray-800 dark:text-gray-200 line-clamp-2">{{ $notification['message'] }}</p>
                        @endif
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $notification['time'] }}</p>
                    </div>
                    @if(!$notification['read'])
                        <button wire:click="markRead('{{ $notification['id'] }}')"
                                class="flex-shrink-0 mt-1 w-2 h-2 bg-indigo-500 rounded-full" title="Mark as read"></button>
                    @endif
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                    No notifications yet
                </div>
            @endforelse
        </div>
    </div>

    {{-- Real-time: listen on private user channel for new notifications --}}
    <div x-init="
        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('App.Models.User.{{ auth()->id() }}')
                .notification((notification) => {
                    \$wire.dispatch('notification-received');
                });
        }
    "></div>
</div>
