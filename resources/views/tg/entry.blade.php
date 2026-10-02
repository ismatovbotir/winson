<!DOCTYPE html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<script src="https://telegram.org/js/telegram-web-app.js"></script></head>
<body>
<script>
    // Pick the catalog language from the Telegram user's app language (remembered choice wins).
    (() => {
        let lang = null;
        try { lang = localStorage.getItem('winson.tg.lang'); } catch (e) {}
        const code = window.Telegram?.WebApp?.initDataUnsafe?.user?.language_code || navigator.language || '';
        lang = lang || (code.startsWith('ru') ? 'ru' : 'uz');
        location.replace(@js(url('/tg')) + '/' + lang + location.hash);
    })();
</script>
<noscript><a href="{{ route('tg.home', ['locale' => 'uz']) }}">O'zbekcha</a> · <a href="{{ route('tg.home', ['locale' => 'ru']) }}">Русский</a></noscript>
</body></html>
