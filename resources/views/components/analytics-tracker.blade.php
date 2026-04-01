@php
    $enabled = (bool) config('analytics.enabled', true);
    $provider = config('analytics.provider', 'plausible');
    $plausibleDomain = config('analytics.plausible.domain');
    $plausibleScript = config('analytics.plausible.script_url', 'https://plausible.io/js/script.js');
    $gaMeasurementId = config('analytics.google.measurement_id');
@endphp

@if ($enabled && $provider === 'plausible' && filled($plausibleDomain))
    <script defer data-domain="{{ $plausibleDomain }}" src="{{ $plausibleScript }}"></script>
@endif

@if ($enabled && $provider === 'google' && filled($gaMeasurementId))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaMeasurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);} // eslint-disable-line no-unused-vars
        gtag('js', new Date());
        gtag('config', '{{ $gaMeasurementId }}');
    </script>
@endif
