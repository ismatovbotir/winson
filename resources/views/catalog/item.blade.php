@extends('layouts.app')

@section('title', $product->name.' — Winson')
@section('description', $product->description)

@php
    $gallery = collect([$product->image_url])->merge($product->images->pluck('url'))->filter()->values();
@endphp

@section('content')

    <section class="mx-auto max-w-6xl px-5 py-16 sm:py-20">
        <a href="{{ route('catalog.category', $category) }}" class="text-sm font-medium text-accent-ink hover:text-navy">
            {{ __('site.catalog_pages.item_back', ['category' => $category->name]) }}
        </a>

        <div class="mt-6 grid gap-10 lg:grid-cols-2 lg:items-start">
            <div data-gallery>
                <div class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-2xl border border-line bg-canvas-alt p-8">
                    <img data-gallery-main src="{{ $gallery->first() }}" alt="{{ $product->name }}"
                        class="h-full w-full object-contain">
                </div>

                @if ($gallery->count() > 1)
                    <div class="mt-3 grid grid-cols-4 gap-3 sm:grid-cols-5">
                        @foreach ($gallery as $i => $url)
                            <button type="button" data-gallery-thumb="{{ $url }}"
                                aria-label="{{ __('site.catalog_pages.gallery_thumb', ['n' => $i + 1]) }}"
                                @class([
                                    'flex aspect-square items-center justify-center overflow-hidden rounded-lg border bg-canvas-alt p-2 transition hover:border-accent',
                                    'border-accent' => $i === 0,
                                    'border-line' => $i !== 0,
                                ])>
                                <img src="{{ $url }}" alt="" class="h-full w-full object-contain">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <p class="font-mono text-sm uppercase tracking-[0.2em] text-accent-ink">{{ $category->name }}</p>
                <h1 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">{{ $product->name }}</h1>
                <p class="mt-4 text-ink-soft">{{ $product->description }}</p>

                @if ($product->sensor_type || $product->specs->isNotEmpty())
                    <div class="mt-6 overflow-hidden rounded-lg border border-line">
                        <h2 class="bg-canvas-alt px-4 py-3 text-sm font-semibold uppercase tracking-wide text-accent-ink">
                            {{ __('site.catalog_pages.item_specs_title') }}
                        </h2>
                        <dl class="divide-y divide-line text-sm">
                            @if ($product->sensor_type)
                                <div class="grid grid-cols-2 gap-4 px-4 py-2.5">
                                    <dt class="text-ink-soft">{{ __('site.catalog_pages.sensor_label') }}</dt>
                                    <dd class="font-medium text-navy">{{ __('site.sensors.'.$product->sensor_type) }}</dd>
                                </div>
                            @endif
                            @foreach ($product->specs as $spec)
                                <div class="grid grid-cols-2 gap-4 px-4 py-2.5">
                                    <dt class="text-ink-soft">{{ $spec->label }}</dt>
                                    <dd class="font-medium text-navy">{{ $spec->value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                <div class="mt-8 rounded-2xl bg-navy p-6 text-center sm:text-left">
                    <h2 class="text-xl font-bold text-white">{{ __('site.catalog_pages.item_cta_title') }}</h2>
                    <p class="mt-2 text-sm text-canvas/75">{{ __('site.catalog_pages.item_cta_body') }}</p>
                    @php $contacts = \App\Support\Contacts::all(); @endphp
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-3 sm:justify-start">
                        <a href="{{ \App\Support\Contacts::primaryUrl($product->name) }}"
                            class="inline-block rounded-md bg-accent px-6 py-3 font-semibold text-navy-deep shadow-sm transition hover:brightness-105">
                            {{ __('site.hero.cta_secondary') }}
                        </a>
                        @if ($contacts['telegram_url'])
                            <a href="{{ $contacts['telegram_url'] }}" target="_blank" rel="noopener"
                                class="rounded-md border border-white/30 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Telegram</a>
                        @endif
                        @if ($contacts['whatsapp_url'])
                            <a href="{{ $contacts['whatsapp_url'] }}?text={{ rawurlencode($product->name) }}" target="_blank" rel="noopener"
                                class="rounded-md border border-white/30 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10">WhatsApp</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($similar->isNotEmpty())
            <div class="mt-16">
                <h2 class="text-xl font-bold text-navy">{{ __('site.catalog_pages.similar_title') }}</h2>
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similar as $other)
                        <a href="{{ route('catalog.item', [$other->category, $other]) }}"
                            class="flex items-center gap-3 rounded-lg border border-line bg-canvas-alt p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                            <img src="{{ $other->image_url }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-contain" aria-hidden="true">
                            <span class="font-semibold text-navy">{{ $other->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    @if ($gallery->count() > 1)
        <script>
            document.querySelectorAll('[data-gallery]').forEach((gallery) => {
                const main = gallery.querySelector('[data-gallery-main]');
                const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
                thumbs.forEach((thumb) => thumb.addEventListener('click', () => {
                    main.src = thumb.dataset.galleryThumb;
                    thumbs.forEach((t) => {
                        t.classList.toggle('border-accent', t === thumb);
                        t.classList.toggle('border-line', t !== thumb);
                    });
                }));
            });
        </script>
    @endif

@endsection
