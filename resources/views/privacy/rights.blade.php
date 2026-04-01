<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            My Data Rights
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-300 dark:border-green-700 rounded-lg p-4 text-green-800 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Export Data --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-download mr-2 text-indigo-500"></i>Export Your Data (Right to Portability)
                </h3>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Download a complete JSON archive of all data associated with your account: profile, teams,
                    documents, comments, and activity history.
                </p>
                <form method="POST" action="{{ route('privacy.export') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-file-export mr-2"></i>Request Data Export
                    </button>
                </form>
                @if ($exportPath)
                    <div class="mt-4 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                        <p class="text-sm text-indigo-700 dark:text-indigo-300">
                            <i class="fas fa-check-circle mr-1"></i>
                            Export ready —
                            <a href="{{ route('privacy.export.download') }}" class="underline font-medium">Download JSON</a>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Correct Data --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-edit mr-2 text-yellow-500"></i>Correct Your Data (Right to Rectification)
                </h3>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    You can update your name, email address, and profile photo from your profile settings at any time.
                </p>
                <a href="{{ route('profile.show') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                    <i class="fas fa-user-edit mr-2"></i>Go to Profile Settings
                </a>
            </div>

            {{-- Delete Account --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-red-200 dark:border-red-800">
                <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2">
                    <i class="fas fa-trash-alt mr-2"></i>Delete Your Account (Right to Erasure)
                </h3>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Permanently delete your account and all associated data. This action
                    <strong>cannot be undone</strong>. A data export will be generated automatically before deletion.
                </p>
                <a href="{{ route('profile.show') }}#delete-account"
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Delete Account
                </a>
            </div>

            {{-- Privacy Policy --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-file-contract mr-2 text-green-500"></i>Privacy Policy
                </h3>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Read our full Privacy Policy and GDPR information including data retention schedules and your full set of rights.
                </p>
                <a href="{{ route('privacy.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                    <i class="fas fa-book-open mr-2"></i>Read Privacy Policy
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
