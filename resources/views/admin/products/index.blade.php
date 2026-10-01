@extends('admin.layout')

@section('title', __('admin.products.title'))

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.products.title'),
        'action' => [
            'url' => route('admin.products.create', array_filter(['category' => request('category')])),
            'label' => __('admin.products.create'),
        ],
    ])

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <select name="category" onchange="this.form.submit()"
            class="rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm">
            <option value="">{{ __('admin.products.all_categories') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <noscript><button class="rounded-md border border-line px-3 py-2 text-sm">{{ __('admin.products.filter') }}</button></noscript>
    </form>

    <div class="overflow-x-auto rounded-xl border border-line bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas-alt text-xs uppercase tracking-wide text-ink-soft">
                <tr>
                    <th class="px-4 py-3">{{ __('admin.common.name') }}</th>
                    <th class="hidden px-4 py-3 md:table-cell">{{ __('admin.products.category') }}</th>
                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('admin.products.features') }}</th>
                    <th class="hidden px-4 py-3 text-right sm:table-cell">{{ __('admin.statistics.views_30d') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($products as $product)
                    <tr class="hover:bg-canvas">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->image_url }}" alt="" class="h-9 w-9 shrink-0 object-contain">
                                <div>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="whitespace-nowrap font-medium text-navy hover:text-accent-ink">{{ $product->name_uz }}</a>
                                    <p class="whitespace-nowrap font-mono text-xs text-ink-soft">{{ $product->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="hidden px-4 py-3 text-ink-soft md:table-cell">{{ $product->category?->name }}</td>
                        <td class="hidden px-4 py-3 font-mono text-xs text-ink-soft sm:table-cell">{{ $product->feature_values_count ?: '—' }}</td>
                        <td class="hidden px-4 py-3 text-right font-mono tabular-nums text-ink sm:table-cell">{{ $product->views_30d }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="mr-3 text-sm font-medium text-accent-ink hover:text-navy">{{ __('admin.common.edit') }}</a>
                            @include('admin.partials.delete-button', ['action' => route('admin.products.destroy', $product)])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-ink-soft">{{ __('admin.common.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
