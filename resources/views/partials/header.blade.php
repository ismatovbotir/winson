@php
    $contacts = \App\Support\Contacts::all();
    $menu = \App\Models\MenuItem::header()->filter(fn ($item) => $item->href)->values();
    $current = url()->current();
    // Language switch → this same page in the other language (crawlable, no redirect).
    $alternates = \App\Support\Seo::page()->alternates();
    $switchUrl = fn ($code) => $alternates[$code] ?? route('home', ['locale' => $code]);
    // A link is "current" when it points at this page (or a parent section); anchors never are.
    $isCurrent = fn ($item) => ! str_contains($item->href, '#')
        && ($item->href === $current || ($item->href !== route('home') && str_starts_with($current, rtrim($item->href, '/').'/')));
@endphp

<header data-header class="group sticky top-0 z-50 overflow-x-clip border-b border-line bg-white/80 backdrop-blur-xl backdrop-saturate-150 transition-shadow duration-300 data-[scrolled]:shadow-[0_8px_30px_-12px_rgb(8_28_51/0.25)]">

    {{-- Status strip: what we sell + direct contact + language. --}}
    <div class="hidden bg-navy-deep text-canvas/70 md:block">
        <div class="mx-auto flex h-8 max-w-6xl items-center justify-between gap-6 px-5 font-mono text-[11px] uppercase tracking-[0.14em]">
            <p class="flex items-center gap-3">
                <span class="bg-barcode inline-block h-3 w-7 text-accent" aria-hidden="true"></span>
                <span>{{ __('site.header.strip') }}</span>
            </p>

            <div class="flex items-center gap-5">
                @if ($contacts['phone_url'])
                    <a href="{{ $contacts['phone_url'] }}" class="transition hover:text-white">{{ $contacts['phone'] }}</a>
                @endif
                @if ($contacts['email_url'])
                    <a href="{{ $contacts['email_url'] }}" class="normal-case tracking-normal transition hover:text-white">{{ $contacts['email'] }}</a>
                @endif
                @if ($contacts['telegram_url'])
                    <a href="{{ $contacts['telegram_url'] }}" target="_blank" rel="noopener" class="transition hover:text-white">Telegram</a>
                @endif

                <div class="flex items-center rounded-sm border border-white/15" aria-label="Til / Язык">
                    @foreach (config('app.supported_locales', ['uz', 'ru']) as $code)
                        <a href="{{ $switchUrl($code) }}" hreflang="{{ $code }}" lang="{{ $code }}"
                            @class([
                                'px-2 py-0.5 transition',
                                'bg-accent text-navy-deep' => app()->getLocale() === $code,
                                'hover:text-white' => app()->getLocale() !== $code,
                            ])
                            @if (app()->getLocale() === $code) aria-current="true" @endif>{{ __('site.locale.'.$code) }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Main bar --}}
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-6 px-5 transition-[height] duration-300 group-data-[scrolled]:h-14 md:h-[4.5rem]" data-header-bar>
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="Winson — {{ __('site.header.home') }}">
            <img src="{{ asset('images/logo/winson-logo.png') }}" alt="Winson" width="250" height="80" class="h-8 w-auto sm:h-9">
            <span class="hidden border-l border-line pl-3 font-mono text-[10px] uppercase leading-tight tracking-[0.18em] text-accent-ink lg:block">
                Auto-ID<br><span class="text-ink-soft">{{ __('site.header.since') }}</span>
            </span>
        </a>

        <button type="button" data-search-open aria-label="{{ __('site.search.title') }}"
            class="ml-auto grid h-10 w-10 place-items-center rounded-lg border border-line bg-white text-navy transition hover:border-accent md:hidden">
            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
        </button>

        <input type="checkbox" id="nav-toggle" class="peer sr-only">

        <label for="nav-toggle"
            class="relative z-10 grid h-10 w-10 cursor-pointer place-items-center rounded-lg border border-line bg-white text-navy transition hover:border-accent md:hidden
                   peer-focus-visible:ring-2 peer-focus-visible:ring-accent
                   peer-checked:[&>span>span:first-child]:translate-y-[4px] peer-checked:[&>span>span:first-child]:rotate-45
                   peer-checked:[&>span>span:last-child]:-translate-y-[4px] peer-checked:[&>span>span:last-child]:-rotate-45">
            <span class="sr-only">{{ __('site.header.menu') }}</span>
            <span class="pointer-events-none block w-5" aria-hidden="true">
                <span class="block h-0.5 w-5 rounded bg-navy transition-transform duration-300"></span>
                <span class="mt-1.5 block h-0.5 w-5 rounded bg-navy transition-transform duration-300"></span>
            </span>
        </label>

        {{-- Nav: a slide-down panel on mobile, inline on desktop. --}}
        <nav aria-label="Primary"
            class="invisible absolute inset-x-0 top-full origin-top -translate-y-2 border-b border-line bg-white opacity-0 shadow-xl transition duration-300
                   peer-checked:visible peer-checked:translate-y-0 peer-checked:opacity-100
                   md:visible md:static md:translate-y-0 md:border-0 md:bg-transparent md:opacity-100 md:shadow-none md:transition-none">
            <div class="mx-auto flex max-w-6xl flex-col px-5 py-4 md:flex-row md:items-center md:gap-5 md:p-0 lg:gap-7">
                @foreach ($menu as $i => $item)
                    <a href="{{ $item->href }}" @if ($item->new_tab) target="_blank" rel="noopener noreferrer" @endif
                        @if ($isCurrent($item)) aria-current="page" @endif
                        class="group flex items-baseline gap-4 border-b border-line/70 py-3.5 text-lg font-semibold text-navy last-of-type:border-0
                               md:border-0 md:py-0 md:text-[15px] md:font-medium md:text-ink-soft md:hover:text-navy md:aria-[current=page]:text-navy">
                        <span class="font-mono text-xs text-accent-ink md:hidden">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="nav-laser">{{ $item->label }}</span>
                    </a>
                @endforeach

                {{-- Language switch lives in the status strip on desktop. --}}
                <div class="mt-4 flex items-center gap-2 md:hidden" aria-label="Til / Язык">
                    @foreach (config('app.supported_locales', ['uz', 'ru']) as $code)
                        <a href="{{ $switchUrl($code) }}" hreflang="{{ $code }}" lang="{{ $code }}"
                            @class([
                                'rounded-md border px-3 py-1.5 font-mono text-sm font-semibold',
                                'border-navy bg-navy text-white' => app()->getLocale() === $code,
                                'border-line text-ink-soft' => app()->getLocale() !== $code,
                            ])>{{ __('site.locale.'.$code) }}</a>
                    @endforeach
                </div>

                <button type="button" data-search-open
                    class="hidden items-center gap-2 rounded-lg border border-line bg-canvas px-3 py-2 text-sm text-ink-soft transition hover:border-accent hover:text-navy md:flex lg:w-48">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                    <span class="hidden flex-1 text-left lg:inline">{{ __('site.search.button') }}</span>
                    <kbd class="hidden rounded border border-line bg-white px-1.5 font-mono text-[11px] lg:inline" aria-hidden="true">/</kbd>
                    <span class="sr-only lg:hidden">{{ __('site.search.title') }}</span>
                </button>

                <a href="{{ \App\Support\Contacts::primaryUrl() }}"
                    class="viewfinder group/cta mt-4 inline-flex items-center justify-center gap-2.5 rounded-md bg-navy px-5 py-3 font-semibold text-white shadow-sm transition
                           hover:bg-navy-deep md:mt-0 md:ml-2 md:py-2.5 md:text-[15px]">
                    <span class="bg-barcode inline-block h-3.5 w-5 text-accent transition group-hover/cta:text-scan" aria-hidden="true"></span>
                    {{ __('site.nav.get_a_quote') }}
                </a>
            </div>
        </nav>
    </div>
</header>

<script>
    (() => {
        const header = document.querySelector('[data-header]');
        const onScroll = () => header.toggleAttribute('data-scrolled', window.scrollY > 8);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    })();
</script>
