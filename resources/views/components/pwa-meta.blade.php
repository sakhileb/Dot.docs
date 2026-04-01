<!-- PWA Meta Tags -->
<meta name="theme-color" content="#3B82F6" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta name="apple-mobile-web-app-title" content="Dot.docs" />
<meta name="application-name" content="Dot.docs" />

<!-- PWA Icons -->
<link rel="icon" type="image/png" sizes="192x192" href="/images/icon-192x192.png" />
<link rel="icon" type="image/png" sizes="512x512" href="/images/icon-512x512.png" />
<link rel="apple-touch-icon" href="/images/icon-192x192.png" />

<!-- Manifest -->
<link rel="manifest" href="{{ route('pwa.manifest') }}" />

<!-- Offline Support -->
<script src="{{ asset('offline.js') }}" defer></script>

<!-- Viewport -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
