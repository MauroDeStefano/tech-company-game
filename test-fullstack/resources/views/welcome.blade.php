<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tech Company Game') }}</title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="Tech Company Game - Gestisci la Tua Software House">
    <meta name="description" content="Un videogioco gestionale dove sviluppi e gestisci la tua software house. Bilanciare sviluppatori, commerciali e progetti per evitare il fallimento!">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Tech Company Game">
    <meta property="og:description" content="Gestisci la tua software house in questo videogioco gestionale">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="Tech Company Game">
    <meta property="twitter:description" content="Gestisci la tua software house in questo videogioco gestionale">
    <meta property="twitter:image" content="{{ asset('images/twitter-image.jpg') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles -->
    @vite(['resources/css/app.css'])

    <!-- Inline critical CSS for above-the-fold content -->
    <style>
        /* Loading screen styles */
        .app-loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            color: white;
            font-size: 1.125rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Hide loading screen when Vue is mounted */
        .vue-mounted .app-loading {
            display: none;
        }
    </style>
</head>
<body class="font-body antialiased bg-neutral-50">
    <!-- Loading screen (shown until Vue loads) -->
    <div class="app-loading">
        <div class="text-center">
            <div class="loading-spinner"></div>
            <div class="loading-text">Caricamento...</div>
        </div>
    </div>

    <!-- Vue.js SPA Mount Point -->
    <div id="app">
        <!-- Vue Router will render here -->
        <router-view></router-view>
    </div>

    <!-- Error Fallback (if Vue fails to load) -->
    <noscript>
        <div style="padding: 2rem; text-align: center; background: #fee2e2; color: #dc2626;">
            <h1>JavaScript Richiesto</h1>
            <p>Questo gioco richiede JavaScript per funzionare. Per favore abilita JavaScript nel tuo browser.</p>
        </div>
    </noscript>

    <!-- Global Configuration for Vue -->
    <script>
        // Make Laravel config available to Vue
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            user: @json(auth()->user()),
            appUrl: '{{ url('/') }}',
            apiUrl: '{{ url('/api') }}',
            locale: '{{ app()->getLocale() }}',
            environment: '{{ app()->environment() }}'
        };

        // Service Worker registration (for PWA features)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    <!-- Analytics (if needed) -->
    @if(config('app.env') === 'production')
        <!-- Google Analytics, Mixpanel, etc. -->
    @endif
</body>
</html>