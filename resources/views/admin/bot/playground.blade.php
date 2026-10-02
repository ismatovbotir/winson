@extends('admin.layout')

@section('title', __('admin.bot.tab_playground'))

@section('content')
    @include('admin.partials.page-header', ['title' => __('admin.bot.title'), 'subtitle' => __('admin.bot.subtitle')])
    @include('admin.bot._tabs')

    @unless ($configured)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ __('admin.bot.ai_not_configured') }}</div>
    @endunless

    <form method="POST" action="{{ route('admin.bot.playground.ask') }}">
        @csrf
        <x-admin.card>
            <p class="-mt-1 mb-3 text-xs text-ink-soft">{{ __('admin.bot.playground_hint') }}</p>
            <textarea name="question" rows="3" required maxlength="2000" placeholder="{{ __('admin.bot.playground_placeholder') }}"
                class="w-full rounded-md border border-line bg-white px-3 py-2 text-sm shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">{{ old('question', $question) }}</textarea>
            <button class="mt-3 rounded-md bg-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-navy-soft">{{ __('admin.bot.ask') }}</button>
        </x-admin.card>
    </form>

    @if ($question)
        <x-admin.card class="mt-6" :title="__('admin.bot.bot_reply')">
            <div class="space-y-3">
                <div class="flex justify-end"><div class="max-w-[85%] whitespace-pre-line rounded-2xl rounded-br-sm bg-navy px-4 py-2.5 text-sm text-white">{{ $question }}</div></div>
                @if ($failed ?? false)
                    <p class="text-sm text-red-600">{{ __('admin.bot.ai_failed') }}</p>
                @else
                    <div class="max-w-[85%] whitespace-pre-line rounded-2xl rounded-bl-sm bg-canvas-alt px-4 py-2.5 text-sm text-ink">{{ $reply->text ?: __('site.tg.guide_intro', [], $locale) }}</div>
                    @foreach ($reply->guides as $guide)
                        <div class="max-w-[85%] rounded-2xl rounded-bl-sm border-2 border-accent bg-white px-4 py-3 text-sm">
                            <p class="mb-1 font-mono text-[11px] uppercase tracking-wide text-accent-ink">{{ __('admin.bot.guide_sent') }} — <a href="{{ route('admin.bot-knowledge.edit', $guide) }}" class="underline">#{{ $guide->id }}</a></p>
                            <p class="whitespace-pre-line text-ink">🛠 {{ $guide->title($locale) }}

{{ $guide->content($locale) }}</p>
                            @if ($guide->images->isNotEmpty())
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    @foreach ($guide->images as $img)
                                        <figure><img src="{{ $img->url }}" alt="" class="w-full rounded border border-line bg-white"><figcaption class="mt-1 text-[11px] text-ink-soft">{{ $img->caption($locale) }}</figcaption></figure>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </x-admin.card>
    @endif
@endsection
