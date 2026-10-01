{{-- Product tile on the category page, with its key characteristics as badges. --}}
@php
    $badgeCodes = ['code_dimension', 'sensor', 'connection', 'ip_rating', 'os'];
    $badges = $product->featureValues
        ->filter(fn ($v) => in_array($v->feature->code, $badgeCodes, true))
        ->groupBy('feature_id')
        ->map(fn ($rows) => $rows->first()->feature->format($rows))
        ->filter()
        ->take(3);
@endphp
<a href="{{ route('catalog.item', [isset($category) ? $category : $product->category, $product]) }}"
    class="group flex gap-4 overflow-hidden rounded-xl border border-line bg-white p-4 transition hover:-translate-y-0.5 hover:border-accent/50 hover:shadow-[0_18px_40px_-20px_rgb(18_58_102/0.45)]">
    <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-lg bg-canvas-alt p-1.5">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="88" height="88" loading="lazy" class="max-h-full max-w-full object-contain">
    </div>
    <div class="min-w-0">
        <h2 class="font-semibold text-navy group-hover:text-accent-ink">{{ $product->name }}</h2>
        <p class="mt-1 line-clamp-2 text-sm text-ink-soft">{{ $product->description }}</p>
        @if ($badges->isNotEmpty())
            <div class="mt-2.5 flex flex-wrap gap-1.5">
                @foreach ($badges as $badge)
                    <span class="rounded bg-canvas-alt px-2 py-0.5 font-mono text-[11px] text-navy">{{ $badge }}</span>
                @endforeach
            </div>
        @endif
    </div>
</a>
