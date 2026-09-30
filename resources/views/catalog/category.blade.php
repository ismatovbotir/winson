@extends('layouts.app')

@section('title', $category->name.' — Winson')

@section('content')

    <section class="mx-auto max-w-6xl px-5 py-16 sm:py-20">
        <a href="{{ route('catalog.index') }}" class="text-sm font-medium text-accent-ink hover:text-navy">
            {{ __('site.catalog_pages.category_back') }}
        </a>

        <div class="mt-4 flex items-center gap-4">
            <img src="{{ $category->image_url }}" alt="" class="h-20 w-20 rounded-xl" aria-hidden="true">
            <h1 class="text-3xl font-bold text-navy sm:text-4xl">{{ $category->name }}</h1>
        </div>

        @if ($category->products->isEmpty())
            <p class="mt-8 max-w-lg rounded-lg border border-line bg-canvas-alt p-5 text-ink-soft">
                {{ __('site.catalog_pages.items_empty') }}
            </p>
        @else
            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($category->products as $product)
                    <a href="{{ route('catalog.item', [$category, $product]) }}"
                        class="group flex gap-4 overflow-hidden rounded-lg border border-line bg-canvas-alt p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                        <img src="{{ $product->image_url }}" alt="" class="h-20 w-20 shrink-0 rounded-xl object-contain" aria-hidden="true">
                        <div>
                            <p class="font-mono text-xs uppercase tracking-wide text-accent-ink">{{ $category->name }}</p>
                            <p class="mt-1 font-semibold text-navy">{{ $product->name }}</p>
                            <p class="mt-1 text-sm text-ink-soft">{{ $product->description }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

@endsection
