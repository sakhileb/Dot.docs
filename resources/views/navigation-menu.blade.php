<nav x-data="{ open: false }" class="app-topbar sticky top-0 z-40 border-b border-sky-100/60 shadow-sm dark:border-white/5">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-[4.75rem] justify-between gap-4 py-3">
            <div class="flex items-center gap-3">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <x-application-mark class="block h-12 w-auto" />
                        <div class="hidden xl:block">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sky-700/70 dark:text-sky-100/70">Dot.docs</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Collaborative Writing OS</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden items-center gap-2 xl:ms-4 sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('documents.index') }}" :active="request()->routeIs('documents.index')">
                        {{ __('Documents') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('templates.index') }}" :active="request()->routeIs('templates.index')">
                        {{ __('Templates') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('documents.transfer') }}" :active="request()->routeIs('documents.transfer')">
                        {{ __('Transfer') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('documents.generate') }}" :active="request()->routeIs('documents.generate')">
                        {{ __('AI Generator') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('ai.analytics') }}" :active="request()->routeIs('ai.analytics')">
                        {{ __('AI Analytics') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-3">
                <!-- Teams Dropdown -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="ms-3 relative">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="app-pill-button inline-flex items-center px-4 py-2 text-sm leading-4 font-medium focus:outline-none transition ease-in-out duration-150">
                                        {{ Auth::user()->currentTeam->name }}

                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60 rounded-[1.5rem] border border-sky-100/70 bg-white/95 p-2 shadow-2xl dark:border-white/8 dark:bg-slate-950/95">
                                    <!-- Team Management -->
                                    <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-400 dark:text-sky-50/45">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <!-- Team Settings -->
                                    <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-dropdown-link>

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-dropdown-link>
                                    @endcan

                                    <!-- Team Switcher -->
                                    @if (Auth::user()->allTeams()->count() > 1)
                                        <div class="my-2 border-t border-sky-100 dark:border-white/8"></div>

                                        <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-400 dark:text-sky-50/45">
                                            {{ __('Switch Teams') }}
                                        </div>

                                        @foreach (Auth::user()->allTeams() as $team)
                                            <x-switchable-team :team="$team" />
                                        @endforeach
                                    @endif
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-sky-300 transition">
                                    <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="app-pill-button inline-flex items-center px-4 py-2 text-sm leading-4 font-medium focus:outline-none transition ease-in-out duration-150">
                                        {{ Auth::user()->name }}

                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-400 dark:text-sky-50/45">
                                {{ __('Manage Account') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <x-dropdown-link href="{{ route('profile.notifications') }}">
                                {{ __('Notification Settings') }}
                            </x-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            <div class="my-2 border-t border-sky-100 dark:border-white/8"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}"
                                         @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="app-pill-button inline-flex items-center justify-center p-2 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-sky-100/70 sm:hidden dark:border-white/8">
        <div class="space-y-2 px-4 pb-4 pt-4">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('documents.index') }}" :active="request()->routeIs('documents.index')">
                {{ __('Documents') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('templates.index') }}" :active="request()->routeIs('templates.index')">
                {{ __('Templates') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('documents.transfer') }}" :active="request()->routeIs('documents.transfer')">
                {{ __('Transfer') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('documents.generate') }}" :active="request()->routeIs('documents.generate')">
                {{ __('AI Generator') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('ai.analytics') }}" :active="request()->routeIs('ai.analytics')">
                {{ __('AI Analytics') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-sky-100/70 px-4 pb-4 pt-4 dark:border-white/8">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-slate-900 dark:text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500 dark:text-sky-50/60">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-2">
                <!-- Account Management -->
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}"
                                   @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="my-2 border-t border-sky-100 dark:border-white/8"></div>

                    <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-400 dark:text-sky-50/45">
                        {{ __('Manage Team') }}
                    </div>

                    <!-- Team Settings -->
                    <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>
                    @endcan

                    <!-- Team Switcher -->
                    @if (Auth::user()->allTeams()->count() > 1)
                        <div class="my-2 border-t border-sky-100 dark:border-white/8"></div>

                        <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-400 dark:text-sky-50/45">
                            {{ __('Switch Teams') }}
                        </div>

                        @foreach (Auth::user()->allTeams() as $team)
                            <x-switchable-team :team="$team" component="responsive-nav-link" />
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </div>
</nav>
