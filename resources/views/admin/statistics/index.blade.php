@extends('admin.layout')

@section('title', __('admin.statistics.title'))

@php
    $n = fn ($v) => number_format($v, 0, '.', ' ');
    $max = max(1, $daily->max('views'));
@endphp

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.statistics.title'),
        'subtitle' => __('admin.statistics.subtitle'),
    ])

    {{-- Period filter --}}
    <nav class="mb-6 flex flex-wrap gap-1 rounded-lg border border-line bg-white p-1 shadow-sm sm:inline-flex" aria-label="{{ __('admin.statistics.title') }}">
        @foreach ($periods as $p)
            <a href="{{ route('admin.statistics', ['days' => $p]) }}"
                @class([
                    'rounded-md px-3 py-1.5 text-sm font-medium transition',
                    'bg-navy text-white' => $days === $p,
                    'text-ink-soft hover:bg-canvas-alt hover:text-navy' => $days !== $p,
                ])>
                {{ __('admin.statistics.periods.'.$p) }}
            </a>
        @endforeach
    </nav>

    {{-- Headline numbers --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
            <p class="text-sm text-ink-soft">{{ __('admin.statistics.views') }}</p>
            <p class="mt-1 font-mono text-4xl font-bold tabular-nums text-navy">{{ $n($totals['views']) }}</p>
        </div>
        <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
            <p class="text-sm text-ink-soft">{{ __('admin.statistics.visitors') }}</p>
            <p class="mt-1 font-mono text-4xl font-bold tabular-nums text-navy">{{ $n($totals['visitors']) }}</p>
            <p class="mt-1 text-xs text-ink-soft">{{ __('admin.statistics.visitors_hint') }}</p>
        </div>
        <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
            <p class="text-sm text-ink-soft">{{ __('admin.statistics.today') }}</p>
            <p class="mt-1 font-mono text-4xl font-bold tabular-nums text-navy">{{ $n($totals['today']) }}</p>
        </div>
    </div>

    {{-- Daily views: single series, so the title names it and no legend is needed. --}}
    <x-admin.card class="mt-6">
        <div class="mb-4 flex items-baseline justify-between gap-4">
            <h2 class="text-base font-semibold text-navy">{{ __('admin.statistics.daily') }}</h2>
            <span class="text-xs text-ink-soft">{{ __('admin.statistics.daily_last', ['days' => $daily->count()]) }}</span>
        </div>

        <div class="relative">
            <span class="absolute left-0 top-0 font-mono text-[11px] text-ink-soft">{{ $n($max) }}</span>
            <div class="absolute inset-x-0 top-2 border-t border-dashed border-line"></div>

            <div class="flex h-44 items-end gap-[2px] border-b border-ink-soft/40 pt-5">
                @foreach ($daily as $day)
                    @php $label = __('admin.statistics.views_on', ['date' => $day['date']->format('d.m.Y'), 'count' => $n($day['views'])]); @endphp
                    <div class="group relative flex h-full flex-1 items-end" tabindex="0" aria-label="{{ $label }}">
                        <div class="w-full rounded-t-[4px] bg-navy transition-colors group-hover:bg-navy-soft group-focus:bg-navy-soft"
                            style="height: {{ $day['views'] ? max(2, round($day['views'] / $max * 100, 1)) : 0 }}%"></div>
                        <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-navy-deep px-2 py-1 text-xs text-white shadow-lg group-hover:block group-focus:block">
                            {{ $label }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-1.5 flex justify-between font-mono text-[11px] text-ink-soft">
                <span>{{ $daily->first()['date']->format('d.m') }}</span>
                <span>{{ $daily->last()['date']->format('d.m') }}</span>
            </div>
        </div>
    </x-admin.card>

    {{-- Top URLs --}}
    <x-admin.card :title="__('admin.statistics.pages')" class="mt-6 !p-0 [&>h2]:px-5 [&>h2]:pt-5 sm:[&>h2]:px-6 sm:[&>h2]:pt-6">
        @if ($pages->isEmpty())
            <p class="px-5 pb-5 text-sm text-ink-soft sm:px-6">{{ __('admin.statistics.empty') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-y border-line bg-canvas-alt text-xs uppercase tracking-wide text-ink-soft">
                        <tr>
                            <th class="px-4 py-2.5 sm:px-6">{{ __('admin.statistics.page') }}</th>
                            <th class="px-4 py-2.5 text-right">{{ __('admin.statistics.views') }}</th>
                            <th class="hidden px-4 py-2.5 text-right sm:table-cell sm:pr-6">{{ __('admin.statistics.visitors') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($pages as $page)
                            <tr class="hover:bg-canvas">
                                <td class="max-w-0 truncate px-4 py-2.5 sm:px-6">
                                    <a href="{{ url($page->path) }}" target="_blank" class="font-mono text-xs text-navy hover:text-accent-ink" title="{{ $page->path }}">{{ $page->path }}</a>
                                </td>
                                <td class="w-24 px-4 py-2.5 text-right font-mono tabular-nums text-ink">{{ $n($page->views) }}</td>
                                <td class="hidden w-32 px-4 py-2.5 text-right font-mono tabular-nums text-ink-soft sm:table-cell sm:pr-6">{{ $n($page->visitors) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>

    {{-- Top items --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2 [&_section]:!p-0 [&_section>h2]:px-5 [&_section>h2]:pt-5 sm:[&_section>h2]:px-6 sm:[&_section>h2]:pt-6">
        @include('admin.statistics._items', [
            'title' => __('admin.statistics.products'),
            'rows' => $products,
            'link' => fn ($m) => route('admin.products.edit', $m),
            'label' => fn ($m) => $m->name,
        ])
        @include('admin.statistics._items', [
            'title' => __('admin.statistics.articles'),
            'rows' => $articles,
            'link' => fn ($m) => route('admin.articles.edit', $m),
            'label' => fn ($m) => $m->title,
        ])
        @include('admin.statistics._items', [
            'title' => __('admin.statistics.categories'),
            'rows' => $categories,
            'link' => fn ($m) => route('admin.categories.edit', $m),
            'label' => fn ($m) => $m->name,
        ])

        <x-admin.card :title="__('admin.statistics.referrers')">
            @if ($referrers->isEmpty())
                <p class="px-5 pb-5 text-sm text-ink-soft sm:px-6">{{ __('admin.statistics.empty') }}</p>
            @else
                <table class="w-full text-left text-sm">
                    <thead class="border-y border-line bg-canvas-alt text-xs uppercase tracking-wide text-ink-soft">
                        <tr>
                            <th class="px-4 py-2.5 sm:px-6">{{ __('admin.statistics.referrer') }}</th>
                            <th class="px-4 py-2.5 text-right sm:pr-6">{{ __('admin.statistics.views') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($referrers as $ref)
                            <tr>
                                <td class="px-4 py-2.5 font-mono text-xs text-navy sm:px-6">{{ $ref->referrer_host }}</td>
                                <td class="px-4 py-2.5 text-right font-mono tabular-nums text-ink sm:pr-6">{{ $n($ref->views) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-admin.card>
    </div>
    {{-- Visitor searches --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @foreach (['searches' => $searches, 'zero' => $zeroSearches] as $kind => $rows)
            <x-admin.card :title="__('admin.statistics.'.($kind === 'zero' ? 'searches_zero' : 'searches'))" class="!p-0 [&>h2]:px-5 [&>h2]:pt-5 sm:[&>h2]:px-6 sm:[&>h2]:pt-6">
                @if ($kind === 'zero')
                    <p class="-mt-3 px-5 pb-3 text-xs text-ink-soft sm:px-6">{{ __('admin.statistics.searches_zero_hint') }}</p>
                @endif
                @if ($rows->isEmpty())
                    <p class="px-5 pb-5 text-sm text-ink-soft sm:px-6">{{ __('admin.statistics.empty') }}</p>
                @else
                    <table class="w-full text-left text-sm">
                        <thead class="border-y border-line bg-canvas-alt text-xs uppercase tracking-wide text-ink-soft">
                            <tr>
                                <th class="px-4 py-2.5 sm:px-6">{{ __('admin.statistics.query') }}</th>
                                <th class="px-4 py-2.5 text-right">{{ __('admin.statistics.times') }}</th>
                                @if ($kind !== 'zero')<th class="hidden px-4 py-2.5 text-right sm:table-cell sm:pr-6">{{ __('admin.statistics.results') }}</th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($rows as $row)
                                <tr class="hover:bg-canvas">
                                    <td class="px-4 py-2.5 sm:px-6">
                                        <a href="{{ route('search.index', ['q' => $row->query]) }}" target="_blank" class="font-medium text-navy hover:text-accent-ink">{{ $row->query }}</a>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono tabular-nums text-ink">{{ $n($row->times) }}</td>
                                    @if ($kind !== 'zero')
                                        <td @class(['hidden px-4 py-2.5 text-right font-mono tabular-nums sm:table-cell sm:pr-6', 'text-red-600' => ! $row->results, 'text-ink-soft' => $row->results])>{{ $row->results }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-admin.card>
        @endforeach
    </div>
@endsection
