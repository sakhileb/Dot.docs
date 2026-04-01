{{--
    Cookie consent banner.
    Uses Alpine.js and localStorage so the banner only shows once.
    Strictly-necessary cookies (session/CSRF) are always accepted.
--}}
<div
    x-data="{
        show: false,
        init() {
            this.show = !localStorage.getItem('cookies_accepted');
        },
        accept() {
            localStorage.setItem('cookies_accepted', '1');
            this.show = false;
        },
        decline() {
            localStorage.setItem('cookies_accepted', '0');
            this.show = false;
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="fixed bottom-0 inset-x-0 z-50 p-4"
    style="display: none;"
    role="dialog"
    aria-label="Cookie consent"
>
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">
            <span class="font-semibold text-gray-800 dark:text-white">🍪 We use cookies</span>
            to keep you signed in and improve your experience.
            Strictly-necessary cookies are always active.
            <a href="{{ route('privacy.index') }}" class="underline text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 ml-1">
                Learn more
            </a>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button
                @click="decline"
                class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
            >
                Decline optional
            </button>
            <button
                @click="accept"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition"
            >
                Accept all
            </button>
        </div>
    </div>
</div>
