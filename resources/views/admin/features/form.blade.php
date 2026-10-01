@extends('admin.layout')

@php
    $title = $feature->exists ? __('admin.features.edit') : __('admin.features.create');
    $input = 'w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30';
    $options = old('options', $feature->exists ? $feature->options->map->only(['id', 'label_uz', 'label_ru'])->all() : []);
    if (empty($options)) {
        $options = [['id' => null, 'label_uz' => '', 'label_ru' => '']];
    }
@endphp

@section('title', $title)

@section('content')
    @include('admin.partials.page-header', ['title' => $title])

    <form method="POST" action="{{ $feature->exists ? route('admin.features.update', $feature) : route('admin.features.store') }}" class="space-y-6">
        @csrf
        @if ($feature->exists) @method('PUT') @endif

        <x-admin.card>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 [&>*]:min-w-0">
                <x-admin.field name="name_uz" :label="__('admin.common.name').' — '.__('admin.common.lang_uz')" :value="$feature->name_uz" required />
                <x-admin.field name="name_ru" :label="__('admin.common.name').' — '.__('admin.common.lang_ru')" :value="$feature->name_ru" required />

                <div class="flex flex-col gap-1.5">
                    <label for="f_type" class="text-sm font-medium text-ink">{{ __('admin.features.type') }}</label>
                    <select id="f_type" name="type" class="{{ $input }}" @disabled($feature->exists) data-feature-type>
                        @foreach (\App\Models\Feature::TYPES as $type)
                            <option value="{{ $type }}" @selected(old('type', $feature->type) === $type)>{{ __('admin.features.types.'.$type) }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-ink-soft">{{ $feature->exists ? __('admin.features.type_locked') : __('admin.features.type_hint') }}</p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="f_group" class="text-sm font-medium text-ink">{{ __('admin.features.group') }}</label>
                    <select id="f_group" name="group" class="{{ $input }}">
                        @foreach (\App\Models\Feature::GROUPS as $group)
                            <option value="{{ $group }}" @selected(old('group', $feature->group) === $group)>{{ __('admin.features.groups.'.$group) }}</option>
                        @endforeach
                    </select>
                </div>

                <x-admin.field name="unit_uz" :label="__('admin.features.unit').' (UZ)'" :value="$feature->unit_uz" :hint="__('admin.features.unit_hint')" data-number-only />
                <x-admin.field name="unit_ru" :label="__('admin.features.unit').' (RU)'" :value="$feature->unit_ru" data-number-only />

                <x-admin.field name="code" :label="__('admin.features.code')" :value="$feature->code" :hint="__('admin.features.code_hint')" />
                <x-admin.field name="sort_order" type="number" :label="__('admin.common.sort_order')" :value="$feature->sort_order" />

                <label class="flex items-start gap-3 text-sm sm:col-span-2">
                    <input type="checkbox" name="is_filterable" value="1" @checked(old('is_filterable', $feature->is_filterable)) class="mt-0.5 rounded border-line">
                    <span>
                        <span class="font-medium text-ink">{{ __('admin.features.filterable') }}</span>
                        <span class="mt-0.5 block text-xs text-ink-soft">{{ __('admin.features.filterable_hint') }}</span>
                    </span>
                </label>
            </div>
        </x-admin.card>

        <x-admin.card :title="__('admin.features.options')" data-options-card>
            <p class="-mt-3 mb-4 text-xs text-ink-soft">{{ __('admin.features.options_hint') }}</p>
            <div data-repeater class="space-y-2">
                @foreach (array_values($options) as $i => $row)
                    <div data-repeater-row class="grid grid-cols-[1fr_1fr_auto] gap-2">
                        <input type="hidden" name="options[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                        <input type="text" name="options[{{ $i }}][label_uz]" value="{{ $row['label_uz'] ?? '' }}" placeholder="UZ" class="{{ $input }}">
                        <input type="text" name="options[{{ $i }}][label_ru]" value="{{ $row['label_ru'] ?? '' }}" placeholder="RU" class="{{ $input }}">
                        <button type="button" data-repeater-remove aria-label="{{ __('admin.products.attr_remove') }}"
                            class="grid h-9 w-8 place-items-center rounded-md text-lg text-ink-soft hover:bg-red-50 hover:text-red-600">×</button>
                    </div>
                @endforeach
            </div>
            <button type="button" data-repeater-add class="mt-4 text-sm font-semibold text-accent-ink hover:text-navy">{{ __('admin.features.option_add') }}</button>
        </x-admin.card>

        @include('admin.partials.form-actions', ['cancel' => route('admin.features.index')])
    </form>

    <script>
        (() => {
            const list = document.querySelector('[data-repeater]');
            let index = list.children.length;
            list.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-repeater-remove]');
                if (!btn) return;
                const row = btn.closest('[data-repeater-row]');
                list.children.length > 1 ? row.remove() : row.querySelectorAll('input').forEach((i) => (i.value = ''));
            });
            document.querySelector('[data-repeater-add]').addEventListener('click', () => {
                const row = list.firstElementChild.cloneNode(true);
                row.querySelectorAll('input').forEach((i) => {
                    i.value = '';
                    i.name = i.name.replace(/options\[\d+\]/, `options[${index}]`);
                });
                index++;
                list.appendChild(row);
                row.querySelector('input[type=text]').focus();
            });

            // Options only matter for select/multi; units only for numbers.
            const type = document.querySelector('[data-feature-type]');
            const sync = () => {
                const t = type.value;
                document.querySelector('[data-options-card]').hidden = !['select', 'multi'].includes(t);
                document.querySelectorAll('[data-number-only]').forEach((el) => (el.hidden = t !== 'number'));
            };
            type.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
