@extends('admin.layout')

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.dashboard.title'),
        'subtitle' => __('admin.dashboard.subtitle'),
    ])

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach (['categories' => 'admin.categories.index', 'products' => 'admin.products.index', 'articles' => 'admin.articles.index'] as $key => $route)
            <a href="{{ route($route) }}" class="group rounded-xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm text-ink-soft">{{ __('admin.dashboard.'.$key) }}</p>
                <p class="mt-1 font-mono text-4xl font-bold text-navy">{{ $counts[$key] }}</p>
                <p class="mt-3 text-sm font-semibold text-accent-ink group-hover:text-navy">{{ __('admin.dashboard.manage') }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('admin.page-content.edit') }}" class="rounded-xl border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
            <p class="font-semibold text-navy">{{ __('admin.nav.page_content') }}</p>
            <p class="mt-1 text-sm text-ink-soft">{{ __('admin.page_content.subtitle') }}</p>
        </a>
        <a href="{{ route('admin.settings.edit') }}" class="rounded-xl border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
            <p class="font-semibold text-navy">{{ __('admin.nav.settings') }}</p>
            <p class="mt-1 text-sm text-ink-soft">{{ __('admin.settings.subtitle') }}</p>
        </a>
    </div>
@endsection
