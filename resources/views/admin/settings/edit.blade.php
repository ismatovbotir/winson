@extends('admin.layout')

@section('title', __('admin.settings.title'))

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
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 [&>*]:min-w-0">
                    @foreach ($settings as $key => $value)
                        <x-admin.field :name="$key" :type="$key === 'contact_email' ? 'email' : 'text'"
                            :label="__('admin.settings.'.$key)" :value="$value" :hint="__('admin.settings.'.$key.'_hint')" />
                    @endforeach
                </div>
            </x-admin.card>
        @endforeach

        @include('admin.partials.form-actions')
    </form>
@endsection
