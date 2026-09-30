@extends('layouts.app')

@section('title', __('site.catalog_pages.index_title').' — Winson')

@section('content')

    <section class="mx-auto max-w-6xl px-5 py-16 sm:py-20">
        <p class="font-mono text-sm uppercase tracking-[0.2em] text-accent-ink">{{ __('site.catalog_pages.index_kicker') }}</p>
        <h1 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">{{ __('site.catalog_pages.index_title') }}</h1>
        <p class="mt-3 max-w-xl text-ink-soft">{{ __('site.catalog_pages.index_subtitle') }}</p>

        <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories as $category)
                @include('partials.category-card')
            @endforeach
        </div>
    </section>

@endsection
