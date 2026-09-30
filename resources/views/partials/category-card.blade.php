{{-- Category tile used on the homepage and /katalog. Expects $category (with products_count). --}}
<a href="{{ route('catalog.category', $category) }}"
    class="group flex flex-col overflow-hidden rounded-xl border border-line bg-white transition duration-300 hover:-translate-y-1 hover:border-accent/50 hover:shadow-[0_18px_40px_-20px_rgb(18_58_102/0.45)]">
    <div class="p-2.5 pb-0">
        <div class="viewfinder flex aspect-[4/3] items-center justify-center rounded-lg bg-canvas-alt p-3 [&::before]:inset-1.5">
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy"
                class="h-full w-full object-contain transition duration-500 ease-out group-hover:-translate-y-1 group-hover:scale-[1.04]">
        </div>
    </div>

    <div class="flex flex-1 flex-col justify-between gap-3 p-4 pt-3.5">
        <span class="font-semibold leading-snug text-navy">{{ $category->name }}</span>
        <span class="flex items-center justify-between font-mono text-[11px] uppercase tracking-[0.14em] text-ink-soft">
            {{ __('site.catalog_pages.items_count', ['count' => $category->products_count]) }}
            <span class="text-base leading-none text-accent-ink transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">→</span>
        </span>
    </div>
</a>
