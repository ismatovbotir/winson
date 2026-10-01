@extends('layouts.app')

@php
    $seo = \App\Support\Seo::page()
        ->title($category->metaTitle() ?: $category->name)
        ->description($category->metaDescription() ?: $category->description ?: __('site.seo.category_description', ['name' => $category->name]))
        ->image($category->image_url)
        ->crumb(__('site.seo.home'), route('home'))
        ->crumb(__('site.catalog_pages.index_kicker'), route('catalog.index'))
        ->crumb($category->name)
        ->node(\App\Support\SchemaOrg::collection($category->name,
            $category->products->map(fn ($p) => ['name' => $p->name, 'url' => route('catalog.item', [$category, $p])])));

    // Filtered variants are thin duplicates of the category — keep them out of the index.
    if ($filter->hasActive()) {
        $seo->noindex();
    }

    $facets = $filter->facets();
    $chips = $filter->chips();
@endphp

@section('content')

    <section class="mx-auto max-w-6xl px-5 py-16 sm:py-20">
        @include('partials.breadcrumbs')

        <div class="mt-4 flex items-center gap-4">
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" width="80" height="80" class="h-20 w-20 rounded-xl">
            <h1 class="text-3xl font-bold text-navy sm:text-4xl">{{ $category->name }}</h1>
        </div>

        @if ($category->description)
            <div class="mt-6 max-w-3xl space-y-3 text-ink-soft">
                @foreach (preg_split('/\R\s*\R/u', trim($category->description)) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        @endif

        @if ($category->products->isEmpty())
            <p class="mt-8 max-w-lg rounded-lg border border-line bg-canvas-alt p-5 text-ink-soft">
                {{ __('site.catalog_pages.items_empty') }}
            </p>
        @else
            <div @class(['mt-10 grid gap-8', 'lg:grid-cols-[260px_1fr]' => $facets])>
                @if ($facets)
                    @include('catalog._filters')
                @endif

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="mr-2 font-mono text-xs uppercase tracking-[0.14em] text-ink-soft">
                            {{ __('site.filters.found', ['count' => $products->count()]) }}
                        </p>
                        @foreach ($chips as $chip)
                            <a href="{{ $chip['url'] }}"
                                class="inline-flex items-center gap-1.5 rounded-full border border-accent/40 bg-accent-soft px-3 py-1 text-sm text-accent-ink transition hover:border-accent">
                                {{ $chip['label'] }} <span aria-hidden="true">×</span>
                                <span class="sr-only">{{ __('site.filters.remove') }}</span>
                            </a>
                        @endforeach
                        @if ($chips)
                            <a href="{{ $filter->resetUrl() }}" class="text-sm font-medium text-ink-soft underline-offset-2 hover:text-navy hover:underline">{{ __('site.filters.reset') }}</a>
                        @endif
                    </div>

                    @if ($products->isEmpty())
                        <div class="mt-6 rounded-lg border border-line bg-canvas-alt p-6 text-ink-soft">
                            <p>{{ __('site.filters.none') }}</p>
                            <a href="{{ $filter->resetUrl() }}" class="mt-3 inline-block font-semibold text-accent-ink hover:text-navy">{{ __('site.filters.reset') }} →</a>
                        </div>
                    @else
                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $facets ? 'xl:grid-cols-2' : 'lg:grid-cols-3' }}">
                            @foreach ($products as $product)
                                @include('catalog._product-card')
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </section>

@endsection
