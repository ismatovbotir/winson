@extends('admin.layout')

@php $title = $category->exists ? __('admin.categories.edit') : __('admin.categories.create'); @endphp

@section('title', $title)

@section('content')
    @include('admin.partials.page-header', ['title' => $title])

    <form method="POST" enctype="multipart/form-data"
        action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <x-admin.card>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 [&>*]:min-w-0">
                <x-admin.field name="name_uz" :label="__('admin.common.name').' — '.__('admin.common.lang_uz')" :value="$category->name_uz" required />
                <x-admin.field name="name_ru" :label="__('admin.common.name').' — '.__('admin.common.lang_ru')" :value="$category->name_ru" required />
                <x-admin.field name="slug" :label="__('admin.common.slug')" :value="$category->slug" :hint="__('admin.common.slug_hint')" />
                <x-admin.field name="sort_order" type="number" :label="__('admin.common.sort_order')" :value="$category->sort_order ?? 0" :hint="__('admin.common.sort_order_hint')" />
                <x-admin.field name="description_uz" :rows="5" :label="__('admin.categories.intro').' — '.__('admin.common.lang_uz')" :value="$category->description_uz" :hint="__('admin.categories.intro_hint')" />
                <x-admin.field name="description_ru" :rows="5" :label="__('admin.categories.intro').' — '.__('admin.common.lang_ru')" :value="$category->description_ru" />
                <div class="sm:col-span-2">
                    <x-admin.image-field :label="__('admin.common.image')" :url="$category->exists ? $category->image_url : null" />
                </div>
            </div>
        </x-admin.card>

        <div class="mt-6">
            @include('admin.partials.seo-fields', [
                'model' => $category,
                'path' => 'catalog/'.$category->slug,
                'fallback' => collect(['uz', 'ru'])->mapWithKeys(fn ($l) => [$l => [
                    'title' => $category->{'name_'.$l},
                    'description' => $category->{'description_'.$l} ?: __('site.seo.category_description', ['name' => $category->{'name_'.$l}], $l),
                ]])->all(),
            ])
        </div>

        @include('admin.partials.form-actions', ['cancel' => route('admin.categories.index')])
    </form>
@endsection
