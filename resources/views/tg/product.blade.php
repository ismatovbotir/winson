@extends('tg.layout', ['back' => route('tg.category', $category)])

@php
    $gallery = collect([$product->image_url])->merge($product->images->pluck('url'))->filter()->unique()->values();
    $specGroups = \App\Support\FeatureTable::for($product);
@endphp

@section('content')
    @include('tg._header', [
        'title' => $product->name,
        'switch' => ['uz' => route('tg.product', [$category, $product, 'locale' => 'uz']), 'ru' => route('tg.product', [$category, $product, 'locale' => 'ru'])],
    ])

    {{-- Swipeable gallery --}}
    <div class="flex snap-x snap-mandatory gap-2 overflow-x-auto px-3 pb-1 [scrollbar-width:none]">
        @foreach ($gallery as $url)
            <img src="{{ $url }}" alt="{{ $product->name }}" class="tg-card aspect-[4/3] w-[88%] shrink-0 snap-center rounded-2xl object-contain p-3 {{ $gallery->count() === 1 ? 'w-full' : '' }}">
        @endforeach
    </div>

    <section class="tg-card mx-3 mt-3 rounded-2xl p-4">
        <p class="font-mono text-[11px] uppercase tracking-[0.14em] tg-link">{{ $category->name }}</p>
        <p class="mt-2 text-[15px] leading-relaxed">{{ $product->description }}</p>
    </section>

    @if ($specGroups->isNotEmpty() || $product->specs->isNotEmpty())
        <section class="tg-card mx-3 mt-3 overflow-hidden rounded-2xl">
            <h2 class="px-4 pt-4 text-sm font-semibold">{{ __('site.catalog_pages.item_specs_title') }}</h2>
            @foreach ($specGroups as $group => $rows)
                <p class="px-4 pt-3 font-mono text-[10px] uppercase tracking-[0.16em] tg-hint">{{ __('site.filters.groups.'.$group) }}</p>
                <dl class="divide-y tg-sep px-4">
                    @foreach ($rows as [$feature, $value])
                        <div class="grid grid-cols-2 gap-3 py-2 text-sm"><dt class="tg-hint">{{ $feature->name }}</dt><dd class="font-medium">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            @endforeach
            @foreach ($product->specs as $spec)
                <div class="grid grid-cols-2 gap-3 border-t tg-sep px-4 py-2 text-sm"><span class="tg-hint">{{ $spec->label }}</span><span class="font-medium">{{ $spec->value }}</span></div>
            @endforeach
            <div class="h-3"></div>
        </section>
    @endif

    {{-- Request-a-price sheet (driven by Telegram's MainButton; hidden until opened) --}}
    <section id="lead" hidden class="tg-card mx-3 mt-3 rounded-2xl p-4" aria-labelledby="lead-title">
        <h2 id="lead-title" class="font-semibold">{{ __('site.tg.request_title') }}</h2>
        <p class="mt-1 text-xs tg-hint">{{ __('site.tg.request_hint') }}</p>
        <form id="lead-form" class="mt-3 space-y-3" novalidate>
            <label class="block text-sm">
                <span class="tg-hint">{{ __('site.tg.name') }}</span>
                <input name="name" maxlength="120" autocomplete="name" class="tg-input mt-1 w-full rounded-xl px-3 py-2.5 text-[15px]">
            </label>
            <label class="block text-sm">
                <span class="tg-hint">{{ __('site.tg.phone') }}</span>
                <input name="phone" type="tel" inputmode="tel" maxlength="40" autocomplete="tel" placeholder="+998 __ ___ __ __" class="tg-input mt-1 w-full rounded-xl px-3 py-2.5 text-[15px]">
            </label>
            <label class="block text-sm">
                <span class="tg-hint">{{ __('site.tg.comment') }}</span>
                <textarea name="message" rows="3" maxlength="1000" placeholder="{{ __('site.tg.comment_placeholder') }}" class="tg-input mt-1 w-full rounded-xl px-3 py-2.5 text-[15px]"></textarea>
            </label>
            <p id="lead-error" hidden class="text-sm text-red-500" role="alert"></p>
        </form>
    </section>

    <section id="lead-done" hidden class="tg-card mx-3 mt-3 rounded-2xl p-5 text-center" role="status">
        <p class="text-3xl">✅</p>
        <h2 class="mt-2 font-semibold">{{ __('site.tg.sent_title') }}</h2>
        <p class="mt-1 text-sm tg-hint">{{ __('site.tg.sent_body') }}</p>
    </section>

    {{-- Outside Telegram (plain browser): no MainButton, so a normal link. --}}
    <div class="no-tg mx-3 mt-4">
        <a href="{{ \App\Support\Contacts::primaryUrl($product->name) }}" class="tg-btn block rounded-xl py-3 text-center font-semibold">{{ __('site.hero.cta_secondary') }}</a>
    </div>
    <style>.in-telegram .no-tg { display: none; }</style>
@endsection

@push('scripts')
<script>
    (() => {
        const tg = window.Telegram?.WebApp;
        if (!tg || !tg.initData) return; // browser fallback link handles it

        const sheet = document.getElementById('lead');
        const done = document.getElementById('lead-done');
        const form = document.getElementById('lead-form');
        const error = document.getElementById('lead-error');
        const user = tg.initDataUnsafe?.user || {};
        form.name.value = [user.first_name, user.last_name].filter(Boolean).join(' ');

        const texts = { open: @js(__('site.tg.request_price')), send: @js(__('site.tg.send')), close: @js(__('site.tg.close')) };
        let state = 'closed';

        const setButton = (text) => tg.MainButton.setParams({ text, is_visible: true, is_active: true });
        setButton(texts.open);

        tg.MainButton.onClick(async () => {
            if (state === 'closed') {
                state = 'open';
                sheet.hidden = false;
                sheet.scrollIntoView({ behavior: 'smooth' });
                (form.phone.value ? form.message : form.phone).focus();
                setButton(texts.send);
                return;
            }
            if (state === 'done') { tg.close(); return; }

            error.hidden = true;
            tg.MainButton.showProgress();
            try {
                const res = await fetch(@js(route('tg.lead')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({
                        init_data: tg.initData,
                        product: {{ $product->id }},
                        name: form.name.value.trim(),
                        phone: form.phone.value.trim(),
                        message: form.message.value.trim(),
                        locale: @js(app()->getLocale()),
                    }),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || !json.ok) {
                    const first = json.errors ? Object.values(json.errors)[0][0] : @js(__('site.tg.error'));
                    throw new Error(first);
                }
                state = 'done';
                sheet.hidden = true;
                done.hidden = false;
                done.scrollIntoView({ behavior: 'smooth' });
                tg.HapticFeedback?.notificationOccurred('success');
                setButton(texts.close);
            } catch (e) {
                error.textContent = e.message;
                error.hidden = false;
                tg.HapticFeedback?.notificationOccurred('error');
            } finally {
                tg.MainButton.hideProgress();
            }
        });
    })();
</script>
@endpush
