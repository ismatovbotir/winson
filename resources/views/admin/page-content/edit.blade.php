@extends('admin.layout')

@section('title', __('admin.page_content.title'))

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.page_content.title'),
        'subtitle' => __('admin.page_content.subtitle'),
    ])

    <form method="POST" action="{{ route('admin.page-content.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach ($sections as $section => $fields)
            <x-admin.card :title="__('admin.page_content.'.$section)">
                <div class="hidden gap-5 pb-2 text-xs font-medium uppercase tracking-wide text-ink-soft sm:grid sm:grid-cols-2">
                    <span>{{ __('admin.common.lang_uz') }}</span>
                    <span>{{ __('admin.common.lang_ru') }}</span>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($fields as $field)
                        @foreach (['uz', 'ru'] as $lang)
                            <x-admin.field
                                :name="$field.'_'.$lang"
                                :label="__('admin.page_content.fields.'.$field).' ('.strtoupper($lang).')'"
                                :value="$content->{$field.'_'.$lang}"
                                :rows="in_array($field, $long, true) ? 4 : null" />
                        @endforeach
                    @endforeach
                </div>
            </x-admin.card>
        @endforeach

        @include('admin.partials.form-actions')
    </form>
@endsection
