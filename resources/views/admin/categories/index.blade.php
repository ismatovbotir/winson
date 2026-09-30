@extends('admin.layout')

@section('title', __('admin.categories.title'))

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.categories.title'),
        'action' => ['url' => route('admin.categories.create'), 'label' => __('admin.categories.create')],
    ])

    <div class="overflow-x-auto rounded-xl border border-line bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas-alt text-xs uppercase tracking-wide text-ink-soft">
                <tr>
                    <th class="hidden px-4 py-3 sm:table-cell">#</th>
                    <th class="px-4 py-3">{{ __('admin.common.name') }}</th>
                    <th class="hidden px-4 py-3 md:table-cell">{{ __('admin.common.slug') }}</th>
                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('admin.categories.products_count') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($categories as $category)
                    <tr class="hover:bg-canvas">
                        <td class="hidden px-4 py-3 font-mono text-ink-soft sm:table-cell">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $category->image_url }}" alt="" class="h-8 w-8 object-contain">
                                <div>
                                    <p class="font-medium text-navy">{{ $category->name_uz }}</p>
                                    <p class="text-xs text-ink-soft">{{ $category->name_ru }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="hidden px-4 py-3 font-mono text-xs text-ink-soft md:table-cell">{{ $category->slug }}</td>
                        <td class="hidden px-4 py-3 sm:table-cell">
                            <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="font-medium text-accent-ink hover:text-navy">
                                {{ $category->products_count }}
                            </a>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="mr-3 text-sm font-medium text-accent-ink hover:text-navy">{{ __('admin.common.edit') }}</a>
                            @include('admin.partials.delete-button', ['action' => route('admin.categories.destroy', $category)])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-ink-soft">{{ __('admin.common.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
