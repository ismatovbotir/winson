@extends('layouts.app')

@php
    \App\Support\Seo::page()
        ->title($home->t('seo_title') ?: __('site.seo.home_title'))
        ->description($home->t('seo_description') ?: __('site.seo.home_description'));
@endphp

@section('content')

    @if ($banners->isNotEmpty())
        @include('partials.banner-slider')
    @endif

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-navy text-canvas">
        @include('partials.hex-grid', ['id' => 'hero-hex', 'sparks' => $home->hero_sparks])

        <div class="relative mx-auto max-w-6xl px-5 py-20 sm:py-28">
            <p class="font-mono text-sm uppercase tracking-[0.2em] text-accent">{{ $home->t('hero_kicker') }}</p>

            <h1 class="mt-4 max-w-2xl font-mono text-4xl font-bold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                {{ $home->t('hero_title') }}
            </h1>

            <p class="mt-6 max-w-xl text-lg text-canvas/80">
                {{ $home->t('hero_subtitle') }}
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ \App\Support\SafeUrl::href($home->hero_cta_primary_link) ?? route('catalog.index') }}"
                    class="rounded-md bg-accent px-6 py-3 font-semibold text-navy-deep shadow-sm transition hover:brightness-105">
                    {{ $home->t('hero_cta_primary') }}
                </a>
                <a href="{{ \App\Support\SafeUrl::href($home->hero_cta_secondary_link) ?? '#contact' }}"
                    class="rounded-md border border-white/30 px-6 py-3 font-semibold text-white transition hover:bg-white/10">
                    {{ $home->t('hero_cta_secondary') }}
                </a>
            </div>
        </div>
    </section>

    {{-- Product categories --}}
    <section id="products" class="mx-auto max-w-6xl px-5 py-16 sm:py-20">
        <div class="max-w-xl">
            <p class="font-mono text-sm uppercase tracking-[0.2em] text-accent-ink">{{ __('site.catalog.kicker') }}</p>
            <h2 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">{{ __('site.catalog.title') }}</h2>
            <p class="mt-3 text-ink-soft">{{ __('site.catalog.subtitle') }}</p>
        </div>

        <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories as $category)
                @include('partials.category-card')
            @endforeach
        </div>

        <a href="{{ route('catalog.index') }}" class="mt-6 inline-block text-sm font-semibold text-accent-ink hover:text-navy">
            {{ __('site.catalog_pages.index_title') }} →
        </a>
    </section>

    {{-- About band --}}
    <section id="about" class="bg-canvas-alt">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 py-16 sm:py-20 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="font-mono text-sm uppercase tracking-[0.2em] text-accent-ink">{{ $home->t('about_kicker') }}</p>
                <h2 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">{{ $home->t('about_title') }}</h2>
                <p class="mt-4 text-ink-soft">{{ $home->t('about_body') }}</p>
            </div>

            <dl class="grid grid-cols-2 gap-6">
                <div class="rounded-lg border border-line bg-canvas p-5">
                    <dt class="text-sm text-ink-soft">{{ $home->t('about_stat1_label') }}</dt>
                    <dd class="mt-1 font-mono text-3xl font-bold text-navy">{{ $home->t('about_stat1_value') }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-5">
                    <dt class="text-sm text-ink-soft">{{ $home->t('about_stat2_label') }}</dt>
                    <dd class="mt-1 font-mono text-3xl font-bold text-navy">{{ $home->t('about_stat2_value') }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-5">
                    <dt class="text-sm text-ink-soft">{{ $home->t('about_stat3_label') }}</dt>
                    <dd class="mt-1 font-mono text-3xl font-bold text-navy">{{ $home->t('about_stat3_value') }}</dd>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-5">
                    <dt class="text-sm text-ink-soft">{{ $home->t('about_stat4_label') }}</dt>
                    <dd class="mt-1 font-mono text-3xl font-bold text-navy">{{ $home->t('about_stat4_value') }}</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-6xl px-5 py-16 sm:py-20">
        <div class="relative overflow-hidden rounded-2xl bg-navy px-6 py-12 text-center sm:px-12">
            @include('partials.hex-grid', [
                'id' => 'cta-hex',
                'lit' => [[14, 0], [15, 1], [13, 2], [16, 3], [12, 4]],
                'pulse' => [[15, 1]],
            ])
            <h2 class="relative text-2xl font-bold text-white sm:text-3xl">{{ $home->t('cta_title') }}</h2>
            <p class="relative mx-auto mt-3 max-w-md text-canvas/75">{{ $home->t('cta_body') }}</p>
            <a href="{{ \App\Support\Contacts::primaryUrl() }}"
                class="relative mt-6 inline-block rounded-md bg-accent px-6 py-3 font-semibold text-navy-deep shadow-sm transition hover:brightness-105">
                {{ $home->t('cta_button') }}
            </a>
        </div>
    </section>

@endsection
