@extends('admin.layout')

@section('title', __('admin.banners.title'))

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.banners.title'),
        'subtitle' => __('admin.banners.subtitle'),
        'action' => ['url' => route('admin.banners.create'), 'label' => __('admin.banners.create')],
    ])

    @if ($banners->isEmpty())
        <x-admin.card><p class="text-sm text-ink-soft">{{ __('admin.common.empty') }}</p></x-admin.card>
    @else
        <div class="space-y-3">
            @foreach ($banners as $banner)
                <div class="flex flex-col gap-4 rounded-xl border border-line bg-white p-3 shadow-sm sm:flex-row sm:items-center">
                    <a href="{{ route('admin.banners.edit', $banner) }}" class="block aspect-[3/1] w-full shrink-0 overflow-hidden rounded-lg bg-navy sm:w-60">
                        <img src="{{ $banner->image_url }}" alt="" class="h-full w-full object-cover @unless ($banner->is_active) opacity-40 grayscale @endunless">
                    </a>
                    <div class="min-w-0 flex-1 px-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs text-ink-soft">#{{ $banner->sort_order }}</span>
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs font-semibold',
                                'bg-accent-soft text-accent-ink' => $banner->is_active,
                                'bg-canvas-alt text-ink-soft' => ! $banner->is_active,
                            ])>{{ $banner->is_active ? __('admin.banners.active') : __('admin.banners.inactive') }}</span>
                        </div>
                        <p class="mt-1 truncate font-medium text-navy">{{ $banner->title_uz ?: '—' }}</p>
                        <p class="truncate text-sm text-ink-soft">{{ $banner->title_ru }}</p>
                        @if ($banner->link)
                            <p class="mt-1 truncate font-mono text-xs text-ink-soft">→ {{ $banner->link }}</p>
                        @endif
                    </div>
                    <div class="flex shrink-0 gap-3 px-1 sm:flex-col sm:items-end">
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="text-sm font-medium text-accent-ink hover:text-navy">{{ __('admin.common.edit') }}</a>
                        @include('admin.partials.delete-button', ['action' => route('admin.banners.destroy', $banner)])
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
