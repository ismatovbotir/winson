@extends('tg.layout')

@section('content')
    @include('tg._header', [
        'title' => __('site.tg.catalog'),
        'switch' => ['uz' => route('tg.home', ['locale' => 'uz']), 'ru' => route('tg.home', ['locale' => 'ru'])],
    ])

    <p class="px-4 pb-3 text-sm tg-hint">{{ __('site.tg.intro') }}</p>

    <ul class="grid grid-cols-2 gap-2.5 px-3">
        @foreach ($categories as $category)
            <li>
                <a href="{{ route('tg.category', $category) }}" class="tg-card flex h-full flex-col rounded-2xl p-3 active:scale-[0.98] transition">
                    <img src="{{ $category->image_url }}" alt="" class="aspect-square w-full rounded-xl object-contain" loading="lazy">
                    <span class="mt-2 text-sm font-semibold leading-snug">{{ $category->name }}</span>
                    <span class="mt-0.5 text-xs tg-hint">{{ trans_choice('site.search.models', $category->products_count, ['count' => $category->products_count]) }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endsection
