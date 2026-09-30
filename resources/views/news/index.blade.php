@extends('layouts.app')

@section('title', __('site.news.index_title').' — Winson')

@section('content')

    <section class="mx-auto max-w-6xl px-5 py-16 sm:py-20">
        <p class="font-mono text-sm uppercase tracking-[0.2em] text-accent-ink">{{ __('site.news.kicker') }}</p>
        <h1 class="mt-2 max-w-2xl text-3xl font-bold text-navy sm:text-4xl">{{ __('site.news.index_title') }}</h1>
        <p class="mt-3 max-w-xl text-ink-soft">{{ __('site.news.index_subtitle') }}</p>

        @if ($articles->isEmpty())
            <p class="mt-10 text-ink-soft">{{ __('site.news.empty') }}</p>
        @else
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <a href="{{ route('news.show', $article) }}"
                        class="group flex flex-col overflow-hidden rounded-xl border border-line bg-canvas-alt transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="aspect-[16/9] w-full overflow-hidden bg-navy">
                            @if ($article->image_url)
                                <img src="{{ $article->image_url }}" alt=""
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="relative h-full w-full">@include('partials.hex-grid', ['id' => 'nc-'.$article->id, 'lit' => [[14, 1], [15, 2]], 'pulse' => []])</div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <p class="font-mono text-xs uppercase tracking-wide text-accent-ink">
                                {{ $article->published_at->translatedFormat('d.m.Y') }}
                                · {{ __('site.news.read_minutes', ['minutes' => $article->read_minutes]) }}
                            </p>
                            <h2 class="mt-2 font-semibold text-navy">{{ $article->title }}</h2>
                            <p class="mt-2 flex-1 text-sm text-ink-soft">{{ $article->excerpt }}</p>
                            <span class="mt-4 text-sm font-semibold text-accent-ink">{{ __('site.news.read_more') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

@endsection
