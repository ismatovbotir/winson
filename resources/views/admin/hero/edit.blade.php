@extends('admin.layout')

@section('title', __('admin.hero.title'))

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.hero.title'),
        'subtitle' => __('admin.hero.subtitle'),
    ])

    <p class="-mt-4 mb-6 text-sm">
        <a href="{{ url('/') }}" target="_blank" class="font-medium text-accent-ink hover:text-navy">{{ __('admin.nav.view_site') }}</a>
    </p>

    <form method="POST" action="{{ route('admin.hero.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach (['uz', 'ru'] as $lang)
                <x-admin.card :title="__('admin.common.lang_'.$lang)">
                    <div class="space-y-5">
                        @foreach ($fields as $field)
                            <x-admin.field :name="$field.'_'.$lang"
                                :label="__('admin.page_content.fields.'.$field)"
                                :value="$content->{$field.'_'.$lang}"
                                :rows="$field === 'hero_subtitle' ? 4 : null" />
                        @endforeach
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        <x-admin.card :title="__('admin.hero.buttons')">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 [&>*]:min-w-0">
                <x-admin.field name="hero_cta_primary_link" :label="__('admin.hero.primary_link')"
                    :value="$content->hero_cta_primary_link" :hint="__('admin.hero.primary_link_hint')" />
                <x-admin.field name="hero_cta_secondary_link" :label="__('admin.hero.secondary_link')"
                    :value="$content->hero_cta_secondary_link" :hint="__('admin.hero.secondary_link_hint')" />
            </div>
        </x-admin.card>

        <x-admin.card :title="__('admin.hero.animation')">
            <label class="flex items-start gap-3 text-sm text-ink">
                <input type="hidden" name="hero_sparks" value="0">
                <input type="checkbox" name="hero_sparks" value="1" @checked(old('hero_sparks', $content->hero_sparks)) class="mt-0.5 rounded border-line">
                <span>
                    <span class="font-medium">{{ __('admin.hero.sparks') }}</span>
                    <span class="mt-0.5 block text-xs text-ink-soft">{{ __('admin.hero.sparks_hint') }}</span>
                </span>
            </label>
        </x-admin.card>

        @include('admin.partials.seo-fields', [
            'model' => $content,
            'titleField' => 'seo_title',
            'descriptionField' => 'seo_description',
            'fallback' => collect(['uz', 'ru'])->mapWithKeys(fn ($l) => [$l => [
                'title' => __('site.seo.home_title', [], $l),
                'description' => __('site.seo.home_description', [], $l),
            ]])->all(),
        ])

        @include('admin.partials.form-actions')
    </form>
@endsection
