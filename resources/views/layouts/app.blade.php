<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.seo-head')

        @php
            $site = \App\Models\Setting::getMany([
                'google_analytics_id', 'yandex_metrica_id',
                'google_site_verification', 'yandex_site_verification',
            ]);
        @endphp

        @if ($site['google_site_verification'])
            <meta name="google-site-verification" content="{{ $site['google_site_verification'] }}">
        @endif
        @if ($site['yandex_site_verification'])
            <meta name="yandex-verification" content="{{ $site['yandex_site_verification'] }}">
        @endif

        @if ($site['google_analytics_id'])
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($site['google_analytics_id']) }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', @js($site['google_analytics_id']));
            </script>
        @endif

        @if ($site['yandex_metrica_id'])
            <script>
                (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
                (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
                ym({{ (int) $site['yandex_metrica_id'] }}, "init", { clickmap: true, trackLinks: true, accurateTrackBounce: true });
            </script>
        @endif

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                body { background:#f5f8fc; color:#101e36; font-family: ui-sans-serif, system-ui, sans-serif; }
            </style>
        @endif
    </head>
    <body class="min-h-screen bg-canvas font-sans text-ink antialiased">
        @include('partials.header')

        <main>
            @yield('content')
        </main>

        @include('partials.footer')

        @include('partials.search-dialog')
    </body>
</html>
