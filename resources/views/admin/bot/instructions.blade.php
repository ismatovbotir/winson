@extends('admin.layout')

@section('title', __('admin.bot.tab_instructions'))

@section('content')
    @include('admin.partials.page-header', ['title' => __('admin.bot.title'), 'subtitle' => __('admin.bot.subtitle')])
    @include('admin.bot._tabs')

    <form method="POST" action="{{ route('admin.bot.instructions.save') }}">
        @csrf
        @method('PUT')
        <x-admin.card>
            <p class="-mt-1 mb-4 text-xs text-ink-soft">{{ __('admin.bot.instructions_hint') }}</p>
            <textarea name="content" rows="28" spellcheck="false"
                class="w-full rounded-md border border-line bg-canvas px-4 py-3 font-mono text-[13px] leading-relaxed text-ink focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">{{ old('content', $content) }}</textarea>
            @error('content') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
            <p class="mt-2 text-xs text-ink-soft">{{ $overridden ? __('admin.bot.overridden') : __('admin.bot.default_file') }}</p>
        </x-admin.card>
        @include('admin.partials.form-actions')
    </form>

    @if ($overridden)
        <form method="POST" action="{{ route('admin.bot.instructions.reset') }}" class="mt-3" onsubmit="return confirm(@js(__('admin.bot.reset_confirm')))">
            @csrf
            @method('DELETE')
            <button class="text-sm text-red-600 hover:text-red-800">{{ __('admin.bot.reset') }}</button>
        </form>
    @endif
@endsection
