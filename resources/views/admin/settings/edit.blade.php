@extends('admin.layout')

@section('title', __('admin.settings.title'))

@php
    // Keys edited as a textarea / checkbox instead of a single-line input.
    $textareas = ['seo_catalog_description_uz', 'seo_catalog_description_ru', 'seo_news_description_uz', 'seo_news_description_ru', 'seo_same_as'];
@endphp

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.settings.title'),
        'subtitle' => __('admin.settings.subtitle'),
    ])

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach ($groups as $group => $settings)
            <x-admin.card :title="__('admin.settings.groups.'.$group)">
                <p class="-mt-3 mb-5 text-xs text-ink-soft">{{ __('admin.settings.groups.'.$group.'_hint') }}</p>

                @if ($group === 'seo')
                    <div class="mb-6 grid gap-3 rounded-lg border border-line bg-canvas p-4 text-sm sm:grid-cols-2">
                        <p><span class="text-ink-soft">{{ __('admin.settings.sitemap') }}:</span>
                            <a href="{{ route('sitemap') }}" target="_blank" class="font-mono text-accent-ink hover:text-navy">{{ route('sitemap') }}</a></p>
                        <p><span class="text-ink-soft">{{ __('admin.settings.robots') }}:</span>
                            <a href="{{ route('robots') }}" target="_blank" class="font-mono text-accent-ink hover:text-navy">{{ route('robots') }}</a></p>
                        <p class="text-xs text-ink-soft sm:col-span-2">{{ __('admin.settings.sitemap_hint') }}</p>
                    </div>

                    <label class="mb-6 flex items-start gap-3 rounded-lg border border-line p-4 text-sm has-[:not(:checked)]:border-red-300 has-[:not(:checked)]:bg-red-50">
                        <input type="hidden" name="seo_indexing" value="0">
                        <input type="checkbox" name="seo_indexing" value="1" @checked(old('seo_indexing', $settings['seo_indexing'] ?? '1') !== '0') class="mt-0.5 rounded border-line">
                        <span>
                            <span class="font-medium text-ink">{{ __('admin.settings.seo_indexing') }}</span>
                            <span class="mt-0.5 block text-xs text-ink-soft">{{ __('admin.settings.seo_indexing_hint') }}</span>
                        </span>
                    </label>
                @endif

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 [&>*]:min-w-0">
                    @foreach ($settings as $key => $value)
                        @continue($key === 'seo_indexing')
                        <x-admin.field :name="$key" :type="$key === 'contact_email' ? 'email' : 'text'"
                            :rows="in_array($key, $textareas, true) ? 3 : null"
                            :class="$key === 'seo_same_as' ? 'sm:col-span-2' : ''"
                            :label="__('admin.settings.'.$key)" :value="$value" :hint="__('admin.settings.'.$key.'_hint')" />
                    @endforeach
                </div>
            </x-admin.card>
        @endforeach

        @include('admin.partials.form-actions')
    </form>

    @include('admin.settings._mcp')
@endsection
