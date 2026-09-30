@extends('admin.layout')

@php $title = $banner->exists ? __('admin.banners.edit') : __('admin.banners.create'); @endphp

@section('title', $title)

@section('content')
    @include('admin.partials.page-header', ['title' => $title, 'subtitle' => __('admin.banners.optional')])

    <form method="POST" enctype="multipart/form-data"
        action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}"
        class="space-y-6">
        @csrf
        @if ($banner->exists) @method('PUT') @endif

        <x-admin.card>
            <div class="space-y-5">
                @if ($banner->image_url)
                    <div class="aspect-[3/1] w-full overflow-hidden rounded-lg bg-navy">
                        <img src="{{ $banner->image_url }}" alt="" class="h-full w-full object-cover">
                    </div>
                @endif

                <div class="flex flex-col gap-1.5">
                    <label for="f_image" class="text-sm font-medium text-ink">
                        {{ __('admin.banners.image') }} @unless ($banner->exists)<span class="text-accent-ink">*</span>@endunless
                    </label>
                    <input id="f_image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" @required(! $banner->exists)
                        class="min-w-0 max-w-full text-sm text-ink-soft file:mr-3 file:rounded-md file:border-0 file:bg-navy file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-navy-soft">
                    <p class="text-xs text-ink-soft">{{ __('admin.banners.image_hint') }}</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 [&>*]:min-w-0">
                    <x-admin.field name="link" :label="__('admin.banners.link')" :value="$banner->link" :hint="__('admin.banners.link_hint')" />
                    <x-admin.field name="sort_order" type="number" :label="__('admin.common.sort_order')" :value="$banner->sort_order" :hint="__('admin.common.sort_order_hint')" />
                </div>

                <label class="flex items-center gap-2 text-sm font-medium text-ink">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active)) class="rounded border-line">
                    {{ __('admin.banners.is_active') }}
                </label>
            </div>
        </x-admin.card>

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach (['uz', 'ru'] as $lang)
                <x-admin.card :title="__('admin.common.lang_'.$lang)">
                    <div class="space-y-5">
                        <x-admin.field :name="'title_'.$lang" :label="__('admin.banners.title_field')" :value="$banner->{'title_'.$lang}" />
                        <x-admin.field :name="'text_'.$lang" :rows="3" :label="__('admin.banners.text')" :value="$banner->{'text_'.$lang}" />
                        <x-admin.field :name="'button_'.$lang" :label="__('admin.banners.button')" :value="$banner->{'button_'.$lang}" />
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        @include('admin.partials.form-actions', ['cancel' => route('admin.banners.index')])
    </form>
@endsection
