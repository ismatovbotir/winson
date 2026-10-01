@extends('admin.layout')

@section('title', __('admin.features.title'))

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.features.title'),
        'subtitle' => __('admin.features.subtitle'),
        'action' => ['url' => route('admin.features.create'), 'label' => __('admin.features.create')],
    ])

    <div class="space-y-6">
        @foreach (\App\Models\Feature::GROUPS as $group)
            @continue(! isset($features[$group]))
            <div class="overflow-x-auto rounded-xl border border-line bg-white shadow-sm">
                <p class="border-b border-line bg-canvas-alt px-4 py-2.5 font-mono text-xs uppercase tracking-[0.14em] text-accent-ink">{{ __('admin.features.groups.'.$group) }}</p>
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-line">
                        @foreach ($features[$group] as $feature)
                            <tr class="hover:bg-canvas">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.features.edit', $feature) }}" class="font-medium text-navy hover:text-accent-ink">{{ $feature->name }}</a>
                                    <p class="font-mono text-xs text-ink-soft">{{ $feature->code }}</p>
                                </td>
                                <td class="hidden px-4 py-3 text-ink-soft sm:table-cell">
                                    {{ __('admin.features.types.'.$feature->type) }}
                                    @if ($feature->hasOptions()) · {{ $feature->options_count }} {{ __('admin.features.options_short') }} @endif
                                    @if ($feature->unit) · {{ $feature->unit }} @endif
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell">
                                    @if ($feature->is_filterable)
                                        <span class="rounded-full bg-accent-soft px-2 py-0.5 text-xs font-semibold text-accent-ink">{{ __('admin.features.in_filters') }}</span>
                                    @endif
                                </td>
                                <td class="hidden whitespace-nowrap px-4 py-3 text-xs text-ink-soft sm:table-cell">{{ __('admin.features.used_by', ['count' => $feature->values_count]) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a href="{{ route('admin.features.edit', $feature) }}" class="mr-3 text-sm font-medium text-accent-ink hover:text-navy">{{ __('admin.common.edit') }}</a>
                                    @include('admin.partials.delete-button', ['action' => route('admin.features.destroy', $feature)])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@endsection
