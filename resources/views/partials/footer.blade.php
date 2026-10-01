<footer id="contact" class="relative overflow-hidden bg-navy text-canvas">
    @include('partials.hex-grid', [
        'id' => 'footer-hex',
        'lit' => [[13, 1], [14, 2], [16, 1], [12, 4], [15, 4], [17, 3]],
        'pulse' => [[14, 2], [15, 4]],
    ])

    <div class="relative mx-auto max-w-6xl px-5 py-14">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <span class="inline-block rounded-md bg-canvas px-3 py-2">
                    <img src="{{ asset('images/logo/winson-logo.png') }}" alt="Winson" class="h-7 w-auto">
                </span>
                <p class="mt-3 max-w-xs text-sm text-canvas/70">
                    {{ __('site.footer.tagline') }}
                </p>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">{{ __('site.footer.products_heading') }}</p>
                <ul class="mt-3 space-y-2 text-sm text-canvas/80">
                    @foreach (once(fn () => \App\Models\Category::orderBy('sort_order')->get(['id', 'slug', 'name_uz', 'name_ru'])) as $footerCategory)
                        <li><a href="{{ route('catalog.category', $footerCategory) }}" class="hover:text-white">{{ $footerCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">{{ __('site.footer.company_heading') }}</p>
                <ul class="mt-3 space-y-2 text-sm text-canvas/80">
                    <li><a href="{{ route('home') }}#about" class="hover:text-white">{{ __('site.footer.about_us') }}</a></li>
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-white">{{ __('site.nav.products') }}</a></li>
                    <li><a href="{{ route('news.index') }}" class="hover:text-white">{{ __('site.nav.news') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">{{ __('site.footer.contact_heading') }}</p>
                @php $contacts = \App\Support\Contacts::all(); @endphp
                <ul class="mt-3 space-y-2 text-sm text-canvas/80">
                    @if ($contacts['phone_url'])
                        <li><a href="{{ $contacts['phone_url'] }}" class="hover:text-white">{{ $contacts['phone'] }}</a></li>
                    @endif
                    @if ($contacts['email_url'])
                        <li><a href="{{ $contacts['email_url'] }}" class="hover:text-white">{{ $contacts['email'] }}</a></li>
                    @endif
                    @if ($contacts['whatsapp_url'])
                        <li><a href="{{ $contacts['whatsapp_url'] }}" target="_blank" rel="noopener" class="hover:text-white">WhatsApp</a></li>
                    @endif
                    @if ($contacts['telegram_url'])
                        <li><a href="{{ $contacts['telegram_url'] }}" target="_blank" rel="noopener" class="hover:text-white">Telegram · @{{ $contacts['telegram'] }}</a></li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-2 border-t border-white/10 pt-6 text-xs text-canvas/50 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ __('site.footer.copyright', ['year' => date('Y')]) }}</p>
            @if (\App\Support\Contacts::all()['address'])
                <p>{{ \App\Support\Contacts::all()['address'] }}</p>
            @endif
        </div>
    </div>
</footer>
