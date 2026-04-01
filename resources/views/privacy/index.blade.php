<x-guest-layout>
    <div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                Privacy Policy &amp; GDPR Information
            </h2>

            {{-- Last Updated --}}
            <p class="text-sm text-gray-500 dark:text-gray-400">Last updated: {{ now()->format('F j, Y') }}</p>

            {{-- Introduction --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">1. Introduction</h3>
                <p class="text-gray-600 dark:text-gray-300">
                    {{ config('app.name') }} ("we", "us") is committed to protecting your personal data in accordance with
                    the General Data Protection Regulation (GDPR) and applicable data protection laws. This policy
                    explains what data we collect, how we use it, and what rights you have.
                </p>
            </div>

            {{-- Data We Collect --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">2. Data We Collect</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-300">
                    <li><strong>Account data:</strong> name, email address, profile photo</li>
                    <li><strong>Document content:</strong> text you create and edit, stored encrypted at rest</li>
                    <li><strong>Collaboration data:</strong> comments, shares, team memberships</li>
                    <li><strong>Usage data:</strong> activity logs, audit trails for security purposes</li>
                    <li><strong>Technical data:</strong> IP address, browser type, session identifiers</li>
                </ul>
            </div>

            {{-- Legal Basis --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">3. Legal Basis for Processing</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-300">
                    <li><strong>Contract performance:</strong> to provide the document collaboration service</li>
                    <li><strong>Legitimate interests:</strong> security logging, abuse prevention</li>
                    <li><strong>Consent:</strong> optional analytics and non-essential cookies</li>
                </ul>
            </div>

            {{-- Data Retention --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">4. Data Retention</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-600 dark:text-gray-300">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 pr-4 font-semibold">Data Type</th>
                                <th class="text-left py-2 font-semibold">Retention Period</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr><td class="py-2 pr-4">Account &amp; documents</td><td>Until account deleted</td></tr>
                            <tr><td class="py-2 pr-4">Audit logs</td><td>365 days</td></tr>
                            <tr><td class="py-2 pr-4">Activity history</td><td>365 days</td></tr>
                            <tr><td class="py-2 pr-4">Auto-save versions</td><td>180 days</td></tr>
                            <tr><td class="py-2 pr-4">Export jobs</td><td>90 days</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Your Rights --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">5. Your Rights</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Under GDPR you have the following rights. To exercise them, visit
                    <a href="{{ route('privacy.rights') }}" class="text-indigo-600 dark:text-indigo-400 underline">My Data Rights</a>
                    in your account settings.
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-300">
                    <li><strong>Right of access</strong> – download a copy of all data we hold about you</li>
                    <li><strong>Right to rectification</strong> – correct inaccurate personal data via profile settings</li>
                    <li><strong>Right to erasure</strong> – delete your account and all associated data</li>
                    <li><strong>Right to portability</strong> – receive your data in a machine-readable format (JSON)</li>
                    <li><strong>Right to restrict processing</strong> – contact us to pause processing pending a complaint</li>
                    <li><strong>Right to object</strong> – object to processing based on legitimate interests</li>
                </ul>
            </div>

            {{-- Cookies --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">6. Cookies</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-4">We use the following cookies:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-300">
                    <li><strong>Strictly necessary:</strong> session cookie, CSRF token (cannot be disabled)</li>
                    <li><strong>Functional:</strong> theme preference, editor settings (stored locally)</li>
                    <li><strong>Analytics:</strong> used only with your consent</li>
                </ul>
            </div>

            {{-- Contact --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">7. Contact</h3>
                <p class="text-gray-600 dark:text-gray-300">
                    For any privacy-related queries, contact us at
                    <a href="mailto:privacy@{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'example.com' }}"
                       class="text-indigo-600 dark:text-indigo-400 underline">
                        privacy@{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'example.com' }}
                    </a>.
                </p>
            </div>

        </div>
    </div>
</x-guest-layout>
