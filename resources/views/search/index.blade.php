@extends('layouts.app')

@php
    $search = app(\App\Support\SiteSearch::class);
    \App\Support\Seo::page()
        ->title($query !== '' ? __('site.search.page_title_q', ['query' => $query]) : __('site.search.page_title'))
        ->description(__('site.search.page_description'))
        ->noindex() // internal search results must not be indexed
        ->crumb(__('site.seo.home'), route('home'))
        ->crumb(__('site.search.title'));
    $total = $results['total'];
@endphp

@section('content')
    <section class="border-b border-line bg-canvas-alt">
        <div class="mx-auto max-w-6xl px-5 py-10 sm:py-14">
            @include('partials.breadcrumbs')
            <h1 class="mt-4 text-3xl font-bold text-navy sm:text-4xl">
                @if ($query !== '' && ! $tooShort)
                    {{ __('site.search.results_for') }} «{{ $query }}»
                @else
                    {{ __('site.search.title') }}
                @endif
            </h1>

            <form action="{{ route('search.index') }}" method="GET" role="search" class="mt-6 flex max-w-2xl gap-2">
                <label for="search-page-q" class="sr-only">{{ __('site.search.title') }}</label>
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-accent-ink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                    <input id="search-page-q" type="search" name="q" value="{{ $query }}" maxlength="100" enterkeyhint="search"
                        placeholder="{{ __('site.search.placeholder') }}" @if ($query === '') autofocus @endif
                        class="w-full rounded-lg border border-line bg-white py-3 pl-11 pr-3 text-base text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">
                </div>
                <button type="submit" class="rounded-lg bg-navy px-5 font-semibold text-white transition hover:bg-navy-soft">{{ __('site.search.submit') }}</button>
            </form>

            @if ($query !== '' && ! $tooShort)
                <p class="mt-4 font-mono text-xs uppercase tracking-[0.14em] text-ink-soft" role="status">
                    {{ trans_choice('site.search.found', $total, ['count' => $total]) }}
                </p>
            @elseif ($tooShort)
                <p class="mt-4 text-sm text-ink-soft" role="status">{{ __('site.search.too_short', ['min' => \App\Support\SiteSearch::MIN_LENGTH]) }}</p>
            @endif
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-5 py-10 sm:py-14">
        @if ($query !== '' && ! $tooShort && $total === 0)
            {{-- No results: never a dead end --}}
            <div class="grid gap-8 lg:grid-cols-[1fr_340px]">
                <div>
                    <h2 class="text-xl font-bold text-navy">{{ __('site.search.none_title', ['query' => $query]) }}</h2>
                    <ul class="mt-4 list-inside list-disc space-y-1.5 text-ink-soft marker:text-accent">
                        <li>{{ __('site.search.tip_spelling') }}</li>
                        <li>{{ __('site.search.tip_fewer') }}</li>
                        <li>{{ __('site.search.tip_model') }}</li>
                    </ul>
                    <p class="mt-8 font-mono text-[11px] uppercase tracking-[0.16em] text-ink-soft">{{ __('site.search.browse') }}</p>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <li><a href="{{ route('catalog.category', $category) }}" class="inline-block rounded-full border border-line bg-white px-3 py-1.5 text-sm text-navy transition hover:border-accent hover:bg-accent-soft">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="rounded-2xl bg-navy p-6 text-white">
                    <h2 class="text-lg font-bold">{{ __('site.search.ask_title') }}</h2>
                    <p class="mt-2 text-sm text-canvas/75">{{ __('site.search.ask_body') }}</p>
                    <a href="{{ \App\Support\Contacts::primaryUrl(__('site.search.ask_subject', ['query' => $query])) }}"
                        class="mt-4 inline-block rounded-md bg-accent px-5 py-2.5 font-semibold text-navy-deep transition hover:brightness-105">{{ __('site.nav.get_a_quote') }}</a>
                </div>
            </div>
        @elseif ($total > 0)
            <div class="space-y-12">
                @if ($results['categories']->isNotEmpty())
                    <section aria-labelledby="sr-cat">
                        <h2 id="sr-cat" class="font-mono text-xs uppercase tracking-[0.16em] text-accent-ink">{{ __('site.search.groups.categories') }} · {{ $results['categories']->count() }}</h2>
                        <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($results['categories'] as $item)
                                <li>
                                    <a href="{{ $item['url'] }}" class="flex items-center gap-4 rounded-xl border border-line bg-white p-3 transition hover:border-accent/50 hover:shadow-md">
                                        <img src="{{ $item['image'] }}" alt="" width="56" height="56" loading="lazy" class="h-14 w-14 shrink-0 rounded-lg">
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-navy">{{ $search->highlight($item['title'], $query) }}</span>
                                            <span class="block text-xs text-ink-soft">{{ $item['meta'] }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($results['products']->isNotEmpty())
                    <section aria-labelledby="sr-prod">
                        <h2 id="sr-prod" class="font-mono text-xs uppercase tracking-[0.16em] text-accent-ink">{{ __('site.search.groups.products') }} · {{ $results['products']->count() }}</h2>
                        <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($results['products'] as $item)
                                <li>
                                    <a href="{{ $item['url'] }}" class="group flex gap-4 rounded-xl border border-line bg-white p-4 transition hover:border-accent/50 hover:shadow-md">
                                        <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-canvas-alt p-1.5">
                                            <img src="{{ $item['image'] }}" alt="" width="72" height="72" loading="lazy" class="max-h-full max-w-full object-contain">
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block font-mono text-[11px] uppercase tracking-wide text-accent-ink">{{ $item['meta'] }}</span>
                                            <span class="mt-0.5 block font-semibold text-navy group-hover:text-accent-ink">{{ $search->highlight($item['title'], $query) }}</span>
                                            @if ($item['snippet'])
                                                <span class="mt-1 line-clamp-2 block text-sm text-ink-soft">{{ $search->highlight($item['snippet'], $query) }}</span>
                                            @endif
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($results['articles']->isNotEmpty())
                    <section aria-labelledby="sr-art">
                        <h2 id="sr-art" class="font-mono text-xs uppercase tracking-[0.16em] text-accent-ink">{{ __('site.search.groups.articles') }} · {{ $results['articles']->count() }}</h2>
                        <ul class="mt-4 divide-y divide-line rounded-xl border border-line bg-white">
                            @foreach ($results['articles'] as $item)
                                <li>
                                    <a href="{{ $item['url'] }}" class="group block p-4 transition hover:bg-canvas">
                                        <span class="block font-mono text-[11px] text-ink-soft">{{ $item['meta'] }}</span>
                                        <span class="mt-0.5 block font-semibold text-navy group-hover:text-accent-ink">{{ $search->highlight($item['title'], $query) }}</span>
                                        <span class="mt-1 line-clamp-2 block text-sm text-ink-soft">{{ $search->highlight($item['snippet'], $query) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        @else
            {{-- Empty page (no query yet): offer starting points --}}
            <p class="font-mono text-[11px] uppercase tracking-[0.16em] text-ink-soft">{{ __('site.search.browse') }}</p>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <li><a href="{{ route('catalog.category', $category) }}" class="inline-block rounded-full border border-line bg-white px-3 py-1.5 text-sm text-navy transition hover:border-accent hover:bg-accent-soft">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
