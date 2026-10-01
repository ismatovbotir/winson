{{-- Structured characteristics (admin → Features). Empty = not shown on the site. --}}
@php
    $current = $product->exists
        ? $product->featureValues->groupBy('feature_id')->map(fn ($rows) => [
            'options' => $rows->pluck('feature_option_id')->filter()->map(fn ($id) => (string) $id)->all(),
            'number' => $rows->first()->value_number !== null ? \App\Models\Feature::number($rows->first()->value_number) : null,
        ])
        : collect();
    $value = fn ($f) => old('features.'.$f->id, $f->hasOptions()
        ? ($f->type === 'multi' ? ($current[$f->id]['options'] ?? []) : ($current[$f->id]['options'][0] ?? ''))
        : ($current[$f->id]['number'] ?? ''));
@endphp

<x-admin.card :title="__('admin.products.features')">
    <p class="-mt-3 mb-5 text-xs text-ink-soft">
        {{ __('admin.products.features_hint') }}
        <a href="{{ route('admin.features.index') }}" class="font-medium text-accent-ink hover:text-navy">{{ __('admin.products.features_manage') }}</a>
    </p>

    <div class="space-y-6">
        @foreach ($features as $group => $groupFeatures)
            <details class="group/g rounded-lg border border-line" @if ($loop->first) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-semibold text-navy">
                    <span>{{ __('admin.features.groups.'.$group) }}
                        <span class="ml-1 font-mono text-xs font-normal text-ink-soft">{{ $groupFeatures->filter(fn ($f) => $current->has($f->id))->count() }}/{{ $groupFeatures->count() }}</span>
                    </span>
                    <span aria-hidden="true" class="transition group-open/g:rotate-180">⌄</span>
                </summary>

                <div class="grid gap-x-6 gap-y-5 border-t border-line p-4 sm:grid-cols-2">
                    @foreach ($groupFeatures as $f)
                        @php $v = $value($f); $id = 'feat_'.$f->id; @endphp
                        <div @class(['min-w-0 flex flex-col gap-1.5', 'sm:col-span-2' => $f->type === 'multi'])>
                            <label for="{{ $id }}" class="text-sm font-medium text-ink">{{ $f->name }}</label>

                            @if ($f->type === 'select')
                                <select id="{{ $id }}" name="features[{{ $f->id }}]" class="{{ $input }}">
                                    <option value="">—</option>
                                    @foreach ($f->options as $o)
                                        <option value="{{ $o->id }}" @selected((string) $v === (string) $o->id)>{{ $o->label }}</option>
                                    @endforeach
                                </select>
                            @elseif ($f->type === 'multi')
                                <div id="{{ $id }}" class="flex flex-wrap gap-x-4 gap-y-2 rounded-md border border-line px-3 py-2.5">
                                    @foreach ($f->options as $o)
                                        <label class="flex items-center gap-2 text-sm text-ink">
                                            <input type="checkbox" name="features[{{ $f->id }}][]" value="{{ $o->id }}"
                                                @checked(in_array((string) $o->id, array_map('strval', (array) $v), true)) class="rounded border-line">
                                            {{ $o->label }}
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($f->type === 'boolean')
                                <select id="{{ $id }}" name="features[{{ $f->id }}]" class="{{ $input }}">
                                    <option value="">—</option>
                                    <option value="1" @selected((string) $v === '1')>{{ __('site.filters.yes') }}</option>
                                    <option value="0" @selected((string) $v === '0')>{{ __('site.filters.no') }}</option>
                                </select>
                            @else
                                <div class="flex items-center gap-2">
                                    <input id="{{ $id }}" type="number" step="any" name="features[{{ $f->id }}]" value="{{ $v }}" class="{{ $input }}">
                                    @if ($f->unit)<span class="shrink-0 text-sm text-ink-soft">{{ $f->unit }}</span>@endif
                                </div>
                            @endif
                            @error('features.'.$f->id) <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>
</x-admin.card>
