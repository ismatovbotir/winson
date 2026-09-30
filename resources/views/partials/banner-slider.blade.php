{{-- Homepage banner slider — slides managed in /admin → Banners. Vanilla JS, no library. --}}
<section data-slider class="relative overflow-hidden bg-navy-deep" aria-roledescription="carousel" aria-label="{{ __('site.banner.label') }}">
    <div data-slider-track class="flex transition-transform duration-700 ease-out motion-reduce:transition-none">
        @foreach ($banners as $i => $banner)
            <div class="relative w-full shrink-0" role="group" aria-roledescription="slide"
                aria-label="{{ __('site.banner.slide', ['n' => $i + 1, 'total' => $banners->count()]) }}"
                @if ($i > 0) aria-hidden="true" @endif>
                <img src="{{ $banner->image_url }}" alt="{{ $banner->t('title') ?? '' }}"
                    class="aspect-[4/3] w-full object-cover sm:aspect-[3/1]" @if ($i > 0) loading="lazy" @endif>

                @if ($banner->t('title') || $banner->t('text') || ($banner->href && $banner->t('button')))
                    <div class="absolute inset-0 bg-gradient-to-r from-navy-deep/85 via-navy-deep/45 to-transparent"></div>
                    <div class="absolute inset-0 mx-auto flex max-w-6xl items-center px-5">
                        <div class="max-w-md text-white sm:max-w-lg">
                            @if ($banner->t('title'))
                                <h2 class="text-2xl font-bold leading-tight sm:text-4xl">{{ $banner->t('title') }}</h2>
                            @endif
                            @if ($banner->t('text'))
                                <p class="mt-3 text-sm text-canvas/85 sm:text-lg">{{ $banner->t('text') }}</p>
                            @endif
                            @if ($banner->href && $banner->t('button'))
                                <a href="{{ $banner->href }}" @if ($i > 0) tabindex="-1" @endif
                                    class="mt-5 inline-block rounded-md bg-accent px-5 py-2.5 text-sm font-semibold text-navy-deep shadow-sm transition hover:brightness-105 sm:px-6 sm:py-3 sm:text-base">
                                    {{ $banner->t('button') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @elseif ($banner->href)
                    {{-- Image-only banner: the whole slide is the link. --}}
                    <a href="{{ $banner->href }}" class="absolute inset-0" @if ($i > 0) tabindex="-1" @endif
                        aria-label="{{ __('site.banner.slide', ['n' => $i + 1, 'total' => $banners->count()]) }}"></a>
                @endif
            </div>
        @endforeach
    </div>

    @if ($banners->count() > 1)
        <button type="button" data-slider-prev aria-label="{{ __('site.banner.prev') }}"
            class="absolute left-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-xl text-white backdrop-blur transition hover:bg-white/30 sm:grid">‹</button>
        <button type="button" data-slider-next aria-label="{{ __('site.banner.next') }}"
            class="absolute right-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-xl text-white backdrop-blur transition hover:bg-white/30 sm:grid">›</button>

        <div class="absolute inset-x-0 bottom-3 flex justify-center gap-2">
            @foreach ($banners as $i => $banner)
                <button type="button" data-slider-dot="{{ $i }}"
                    aria-label="{{ __('site.banner.slide', ['n' => $i + 1, 'total' => $banners->count()]) }}"
                    class="h-2 rounded-full bg-white/50 transition-all hover:bg-white aria-[current=true]:w-6 aria-[current=true]:bg-accent {{ $i === 0 ? 'w-6' : 'w-2' }}"
                    @if ($i === 0) aria-current="true" @endif></button>
            @endforeach
        </div>

        <script>
            (() => {
                const root = document.currentScript.closest('[data-slider]');
                const track = root.querySelector('[data-slider-track]');
                const slides = [...track.children];
                const dots = [...root.querySelectorAll('[data-slider-dot]')];
                const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                let index = 0, timer = null, startX = null;

                const go = (i) => {
                    index = (i + slides.length) % slides.length;
                    track.style.transform = `translateX(-${index * 100}%)`;
                    slides.forEach((s, n) => {
                        s.setAttribute('aria-hidden', n !== index);
                        s.querySelectorAll('a').forEach((a) => (a.tabIndex = n === index ? 0 : -1));
                    });
                    dots.forEach((d, n) => {
                        d.setAttribute('aria-current', n === index);
                        d.classList.toggle('w-6', n === index);
                        d.classList.toggle('w-2', n !== index);
                    });
                };
                const play = () => { if (!reduced) { stop(); timer = setInterval(() => go(index + 1), 6000); } };
                const stop = () => clearInterval(timer);

                root.querySelector('[data-slider-prev]').addEventListener('click', () => { go(index - 1); play(); });
                root.querySelector('[data-slider-next]').addEventListener('click', () => { go(index + 1); play(); });
                dots.forEach((d, n) => d.addEventListener('click', () => { go(n); play(); }));
                root.addEventListener('mouseenter', stop);
                root.addEventListener('mouseleave', play);
                root.addEventListener('focusin', stop);
                root.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; stop(); }, { passive: true });
                root.addEventListener('touchend', (e) => {
                    const dx = e.changedTouches[0].clientX - startX;
                    if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
                    play();
                });
                document.addEventListener('visibilitychange', () => (document.hidden ? stop() : play()));
                play();
            })();
        </script>
    @endif
</section>
