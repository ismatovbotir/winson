@php
    // Unmatched URLs don't pass through SetLocale: take the language from the
    // /uz|/ru prefix if there is one, so links and texts match the visitor.
    $supported = config('app.supported_locales', ['uz', 'ru']);
    $segment = request()->segment(1);
    if (in_array($segment, $supported, true)) {
        app()->setLocale($segment);
    }
    \Illuminate\Support\Facades\URL::defaults(['locale' => app()->getLocale()]);

    \App\Support\Seo::page()->title(__('site.seo.not_found_title'))->noindex();
@endphp
@extends('layouts.app')

@section('content')
    <section class="relative overflow-hidden bg-navy text-canvas">
        @include('partials.hex-grid', ['id' => 'nf-hex', 'lit' => [[12, 1], [13, 2], [15, 3]], 'pulse' => [[13, 2]]])
        <div class="relative mx-auto max-w-6xl px-5 py-24 sm:py-32">
            <p class="font-mono text-sm uppercase tracking-[0.2em] text-accent">404</p>
            <h1 class="mt-3 font-mono text-4xl font-bold text-white sm:text-5xl">{{ __('site.seo.not_found_title') }}</h1>
            <p class="mt-5 max-w-lg text-lg text-canvas/80">{{ __('site.seo.not_found_body') }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('catalog.index') }}" class="rounded-md bg-accent px-6 py-3 font-semibold text-navy-deep transition hover:brightness-105">
                    {{ __('site.seo.not_found_catalog') }}
                </a>
                <a href="{{ route('news.index') }}" class="rounded-md border border-white/30 px-6 py-3 font-semibold text-white transition hover:bg-white/10">
                    {{ __('site.seo.not_found_news') }}
                </a>
            </div>
        </div>
    </section>
@endsection
