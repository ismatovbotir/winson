<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Winson</title>
    {{-- Official Telegram Mini App SDK (sets --tg-theme-* CSS variables). --}}
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    @fonts
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --tg-bg: var(--tg-theme-bg-color, #ffffff);
            --tg-bg2: var(--tg-theme-secondary-bg-color, #f2f5fa);
            --tg-text: var(--tg-theme-text-color, #101e36);
            --tg-hint: var(--tg-theme-hint-color, #6b7a90);
            --tg-link: var(--tg-theme-link-color, #0b6e8f);
            --tg-btn: var(--tg-theme-button-color, #123a66);
            --tg-btn-text: var(--tg-theme-button-text-color, #ffffff);
            --tg-sep: color-mix(in oklab, var(--tg-hint) 25%, transparent);
        }
        body { background: var(--tg-bg2); color: var(--tg-text); }
        .tg-card { background: var(--tg-bg); }
        .tg-hint { color: var(--tg-hint); }
        .tg-link { color: var(--tg-link); }
        .tg-sep, .tg-sep > * { border-color: var(--tg-sep); }
        .tg-btn { background: var(--tg-btn); color: var(--tg-btn-text); }
        .tg-input { background: var(--tg-bg2); color: var(--tg-text); border: 1px solid var(--tg-sep); }
        .tg-input:focus { outline: 2px solid color-mix(in oklab, var(--tg-btn) 50%, transparent); }
    </style>
</head>
<body class="min-h-screen font-sans antialiased" data-back="{{ $back ?? '' }}">
    <main class="mx-auto max-w-xl pb-28">
        @yield('content')
    </main>

    <script>
        (() => {
            const tg = window.Telegram?.WebApp;
            document.documentElement.classList.toggle('in-telegram', !!tg?.initData);
            if (!tg) return;
            tg.ready();
            tg.expand();
            try { tg.setHeaderColor('secondary_bg_color'); tg.setBackgroundColor('secondary_bg_color'); } catch (e) {}

            // Native Back button for inner pages.
            const back = document.body.dataset.back;
            if (back) {
                tg.BackButton.show();
                tg.BackButton.onClick(() => { location.href = back; });
            } else {
                tg.BackButton.hide();
            }
            // Haptic tick on navigation taps.
            document.addEventListener('click', (e) => {
                if (e.target.closest('a[href]')) tg.HapticFeedback?.selectionChanged();
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
