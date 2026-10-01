@extends('admin.layout')

@php $title = $article->exists ? __('admin.articles.edit') : __('admin.articles.create'); @endphp

@section('title', $title)

@push('head')
    @vite('resources/js/admin-editor.js')
@endpush

@section('content')
    @include('admin.partials.page-header', ['title' => $title])

    @if ($article->exists)
        <p class="-mt-4 mb-6 text-sm">
            <a href="{{ route('news.show', $article) }}" target="_blank" class="font-medium text-accent-ink hover:text-navy">{{ __('admin.nav.view_site') }}</a>
        </p>
    @endif

    <form method="POST" enctype="multipart/form-data"
        action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
        class="space-y-6">
        @csrf
        @if ($article->exists) @method('PUT') @endif

        <x-admin.card>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 [&>*]:min-w-0">
                <x-admin.field name="published_at" type="date" :label="__('admin.articles.published_at')" :value="$article->published_at?->format('Y-m-d')" required />
                <x-admin.field name="read_minutes" type="number" :label="__('admin.articles.read_minutes')" :value="$article->read_minutes" required />
                <x-admin.field name="slug" :label="__('admin.common.slug')" :value="$article->slug" :hint="__('admin.common.slug_hint')" />
                <div class="sm:col-span-3">
                    <x-admin.image-field :label="__('admin.articles.cover')" :url="$article->image_url" removable />
                </div>
            </div>
        </x-admin.card>

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach (['uz', 'ru'] as $lang)
                <x-admin.card :title="__('admin.common.lang_'.$lang)">
                    <div class="space-y-5">
                        <x-admin.field :name="'title_'.$lang" :label="__('admin.articles.title_field')" :value="$article->{'title_'.$lang}" required />
                        <x-admin.field :name="'excerpt_'.$lang" :rows="3" :label="__('admin.articles.excerpt')" :value="$article->{'excerpt_'.$lang}" required />
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        @foreach (['uz', 'ru'] as $lang)
            <x-admin.card :title="__('admin.articles.body').' — '.__('admin.common.lang_'.$lang)">
                <div data-rich-editor
                    data-upload-url="{{ route('admin.article-images.store') }}"
                    data-placeholder="{{ __('admin.articles.body_placeholder') }}"
                    data-msg-uploading="{{ __('admin.articles.editor_uploading') }}"
                    data-msg-failed="{{ __('admin.articles.editor_failed') }}"
                    data-msg-type="{{ __('admin.articles.editor_type') }}"
                    data-l-normal="{{ __('admin.articles.editor_normal') }}"
                    data-l-h2="{{ __('admin.articles.editor_h2') }}"
                    data-l-h3="{{ __('admin.articles.editor_h3') }}">
                    <input type="hidden" name="body_{{ $lang }}" value="{{ old('body_'.$lang, $article->{'body_'.$lang}) }}">
                    <div data-editor-mount class="rich-editor"></div>
                    <p data-editor-status class="mt-2 min-h-4 text-xs text-ink-soft" role="status"></p>
                </div>
                <p class="text-xs text-ink-soft">{{ __('admin.articles.body_hint') }}</p>
                @error('body_'.$lang)
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </x-admin.card>
        @endforeach

        @include('admin.partials.seo-fields', [
            'model' => $article,
            'path' => 'news/'.$article->slug,
            'fallback' => collect(['uz', 'ru'])->mapWithKeys(fn ($l) => [$l => [
                'title' => $article->{'title_'.$l},
                'description' => $article->{'excerpt_'.$l},
            ]])->all(),
        ])

        @include('admin.partials.form-actions', ['cancel' => route('admin.articles.index')])
    </form>
@endsection
