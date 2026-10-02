@extends('tg.layout', ['back' => route('tg.home')])

@section('content')
    @include('tg._header', [
        'title' => $category->name,
        'switch' => ['uz' => route('tg.category', [$category, 'locale' => 'uz']), 'ru' => route('tg.category', [$category, 'locale' => 'ru'])],
    ])

    <ul class="tg-card mx-3 divide-y tg-sep overflow-hidden rounded-2xl">
        @forelse ($category->products as $product)
            @php
                $badges = \App\Support\FeatureTable::for($product)->flatten(1)
                    ->filter(fn ($r) => in_array($r[0]->code, ['code_dimension', 'connection', 'ip_rating', 'os'], true))
                    ->map(fn ($r) => $r[1])->take(3);
            @endphp
            <li>
                <a href="{{ route('tg.product', [$category, $product]) }}" class="flex items-center gap-3 p-3 active:opacity-70">
                    <img src="{{ $product->image_url }}" alt="" class="h-16 w-16 shrink-0 rounded-xl object-contain" style="background: var(--tg-bg2)" loading="lazy">
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">{{ $product->name }}</span>
                        <span class="mt-0.5 line-clamp-2 block text-xs tg-hint">{{ $product->description }}</span>
                        @if ($badges->isNotEmpty())
                            <span class="mt-1 block truncate font-mono text-[11px] tg-link">{{ $badges->implode(' · ') }}</span>
                        @endif
                    </span>
                    <span class="tg-hint" aria-hidden="true">›</span>
                </a>
            </li>
        @empty
            <li class="p-4 text-sm tg-hint">{{ __('site.catalog_pages.items_empty') }}</li>
        @endforelse
    </ul>
@endsection
