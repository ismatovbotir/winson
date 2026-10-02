<header class="flex items-center justify-between gap-3 px-4 pb-2 pt-4">
    <div class="min-w-0">
        <p class="font-mono text-[11px] uppercase tracking-[0.16em] tg-link">Winson</p>
        <h1 class="truncate text-xl font-bold">{{ $title }}</h1>
    </div>
    <div class="flex shrink-0 overflow-hidden rounded-lg border tg-sep text-xs font-semibold">
        @foreach (['uz', 'ru'] as $code)
            <a href="{{ $switch[$code] }}" onclick="try{localStorage.setItem('winson.tg.lang','{{ $code }}')}catch(e){}"
                @class(['px-2.5 py-1.5', 'tg-btn' => app()->getLocale() === $code, 'tg-hint' => app()->getLocale() !== $code])>{{ strtoupper($code) }}</a>
        @endforeach
    </div>
</header>
