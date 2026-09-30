@extends('layouts.app')

@section('title', $article->title.' — Winson')
@section('description', $article->excerpt)

@section('content')

    <article class="mx-auto max-w-3xl px-5 py-16 sm:py-20">
        <a href="{{ route('news.index') }}" class="text-sm font-medium text-accent-ink hover:text-navy">
            {{ __('site.news.back_to_news') }}
        </a>

        <p class="mt-4 font-mono text-xs uppercase tracking-wide text-accent-ink">
            {{ $article->published_at->translatedFormat('d.m.Y') }}
            · {{ __('site.news.read_minutes', ['minutes' => $article->read_minutes]) }}
        </p>

        <h1 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">{{ $article->title }}</h1>

        @if ($article->image_url)
            <div class="mt-6 overflow-hidden rounded-xl bg-navy">
                <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full">
            </div>
        @endif

        {{-- Body is HTML sanitized on save (App\Support\RichText::clean). --}}
        <div class="prose-winson mt-8">
            {!! $article->body !!}
        </div>

        @if ($others->isNotEmpty())
            <div class="mt-16 border-t border-line pt-10">
                <h2 class="text-xl font-bold text-navy">{{ __('site.news.related_title') }}</h2>
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach ($others as $other)
                        <a href="{{ route('news.show', $other) }}"
                            class="group overflow-hidden rounded-lg border border-line bg-canvas-alt transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="aspect-[16/9] w-full overflow-hidden bg-navy">
                                @if ($other->image_url)
                                    <img src="{{ $other->image_url }}" alt=""
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <div class="relative h-full w-full">@include('partials.hex-grid', ['id' => 'nc-'.$other->id, 'lit' => [[14, 1], [15, 2]], 'pulse' => []])</div>
                                @endif
                            </div>
                            <p class="p-4 font-semibold text-navy">{{ $other->title }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </article>

@endsection
