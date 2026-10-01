@extends('admin.layout')

@php
    $title = $product->exists ? __('admin.products.edit') : __('admin.products.create');

    $specRows = old('specs', $product->exists
        ? $product->specs->map->only(['label_uz', 'label_ru', 'value_uz', 'value_ru'])->all()
        : []);
    if (empty($specRows)) {
        $specRows = [['label_uz' => '', 'label_ru' => '', 'value_uz' => '', 'value_ru' => '']];
    }

    $relatedIds = collect(old('related', $product->exists ? $product->relatedProducts->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)->all();

    $input = 'w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30';
@endphp

@section('title', $title)

@section('content')
    @include('admin.partials.page-header', ['title' => $title])

    @if ($product->exists && $product->category)
        <p class="-mt-4 mb-6 text-sm">
            <a href="{{ route('catalog.item', [$product->category, $product]) }}" target="_blank" class="font-medium text-accent-ink hover:text-navy">
                {{ __('admin.nav.view_site') }}
            </a>
        </p>
    @endif

    <form method="POST" enctype="multipart/form-data"
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
        class="space-y-6">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <x-admin.card>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 [&>*]:min-w-0">
                <div class="flex flex-col gap-1.5 sm:col-span-2">
                    <label for="f_category_id" class="text-sm font-medium text-ink">{{ __('admin.products.category') }} <span class="text-accent-ink">*</span></label>
                    <select id="f_category_id" name="category_id" required class="{{ $input }}">
                        <option value=""></option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>


                <x-admin.field name="name_uz" :label="__('admin.common.name').' — '.__('admin.common.lang_uz')" :value="$product->name_uz" required />
                <x-admin.field name="name_ru" :label="__('admin.common.name').' — '.__('admin.common.lang_ru')" :value="$product->name_ru" required />
                <x-admin.field name="description_uz" :rows="5" :label="__('admin.common.description').' — '.__('admin.common.lang_uz')" :value="$product->description_uz" />
                <x-admin.field name="description_ru" :rows="5" :label="__('admin.common.description').' — '.__('admin.common.lang_ru')" :value="$product->description_ru" />
                <x-admin.field name="slug" :label="__('admin.common.slug')" :value="$product->slug" :hint="__('admin.common.slug_hint')" />
                <x-admin.field name="sort_order" type="number" :label="__('admin.common.sort_order')" :value="$product->sort_order ?? 0" :hint="__('admin.common.sort_order_hint')" />
            </div>
        </x-admin.card>

        @include('admin.products._features')

        <x-admin.card :title="__('admin.products.attributes')">
            <p class="-mt-3 mb-4 text-xs text-ink-soft">{{ __('admin.products.attributes_hint') }}</p>

            <div class="hidden gap-2 px-1 text-xs font-medium uppercase tracking-wide text-ink-soft md:grid md:grid-cols-[1fr_1fr_1fr_1fr_auto]">
                <span>{{ __('admin.products.attr_label') }} (UZ)</span>
                <span>{{ __('admin.products.attr_label') }} (RU)</span>
                <span>{{ __('admin.products.attr_value') }} (UZ)</span>
                <span>{{ __('admin.products.attr_value') }} (RU)</span>
                <span class="w-8"></span>
            </div>

            <div data-repeater class="mt-2 space-y-3 md:space-y-2">
                @foreach (array_values($specRows) as $i => $row)
                    <div data-repeater-row class="grid gap-2 rounded-lg border border-line p-3 md:grid-cols-[1fr_1fr_1fr_1fr_auto] md:border-0 md:p-0">
                        @foreach (['label_uz', 'label_ru', 'value_uz', 'value_ru'] as $col)
                            <input type="text" name="specs[{{ $i }}][{{ $col }}]" value="{{ $row[$col] ?? '' }}"
                                placeholder="{{ __('admin.products.'.(str_starts_with($col, 'label') ? 'attr_label' : 'attr_value')) }} ({{ strtoupper(substr($col, -2)) }})"
                                class="{{ $input }}">
                        @endforeach
                        <button type="button" data-repeater-remove title="{{ __('admin.products.attr_remove') }}" aria-label="{{ __('admin.products.attr_remove') }}"
                            class="grid h-9 w-8 place-items-center justify-self-end rounded-md text-lg text-ink-soft hover:bg-red-50 hover:text-red-600">×</button>
                    </div>
                @endforeach
            </div>

            <button type="button" data-repeater-add class="mt-4 text-sm font-semibold text-accent-ink hover:text-navy">
                {{ __('admin.products.attr_add') }}
            </button>
        </x-admin.card>

        <x-admin.card :title="__('admin.products.images')">
            <div class="space-y-5">
                <x-admin.image-field :label="__('admin.products.cover')" :url="$product->exists ? $product->image_url : null" />

                <div>
                    <p class="mb-2 text-sm font-medium text-ink">{{ __('admin.products.gallery') }}</p>
                    @if ($product->exists && $product->images->isNotEmpty())
                        <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                            @foreach ($product->images as $image)
                                <label class="group relative block cursor-pointer overflow-hidden rounded-lg border border-line bg-canvas-alt p-2 has-[:checked]:border-red-400 has-[:checked]:opacity-50">
                                    <img src="{{ $image->url }}" alt="" class="aspect-square w-full object-contain">
                                    <span class="mt-2 flex items-center gap-1.5 text-xs text-ink-soft">
                                        <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="rounded border-line">
                                        {{ __('admin.products.gallery_remove') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-ink-soft">{{ __('admin.products.gallery_empty') }}</p>
                    @endif
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="f_gallery" class="text-sm font-medium text-ink">{{ __('admin.products.gallery_upload') }}</label>
                    <input id="f_gallery" name="gallery[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp"
                        class="text-sm text-ink-soft file:mr-3 file:rounded-md file:border-0 file:bg-navy file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-navy-soft">
                    <p class="text-xs text-ink-soft">{{ __('admin.products.gallery_hint') }}</p>
                </div>
            </div>
        </x-admin.card>

        @include('admin.partials.seo-fields', [
            'model' => $product,
            'path' => $product->exists && $product->category ? 'katalog/'.$product->category->slug.'/'.$product->slug : 'katalog',
            'fallback' => collect(['uz', 'ru'])->mapWithKeys(fn ($l) => [$l => [
                'title' => $product->exists ? $product->{'name_'.$l}.($product->category ? ' — '.$product->category->{'name_'.$l} : '') : '',
                'description' => $product->{'description_'.$l},
            ]])->all(),
        ])

        <x-admin.card :title="__('admin.products.related')">
            <select name="related[]" multiple size="10" class="{{ $input }}">
                @foreach ($allProducts->groupBy(fn ($p) => $p->category?->name) as $categoryName => $group)
                    <optgroup label="{{ $categoryName }}">
                        @foreach ($group as $option)
                            <option value="{{ $option->id }}" @selected(in_array($option->id, $relatedIds, true))>{{ $option->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-ink-soft">{{ __('admin.products.related_hint') }}</p>
        </x-admin.card>

        @include('admin.partials.form-actions', ['cancel' => route('admin.products.index')])
    </form>

    <script>
        document.querySelectorAll('[data-repeater]').forEach((list) => {
            const addBtn = list.parentElement.querySelector('[data-repeater-add]');
            let index = list.querySelectorAll('[data-repeater-row]').length;

            list.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-repeater-remove]');
                if (!btn) return;
                const row = btn.closest('[data-repeater-row]');
                if (list.querySelectorAll('[data-repeater-row]').length > 1) {
                    row.remove();
                } else {
                    row.querySelectorAll('input').forEach((input) => (input.value = ''));
                }
            });

            addBtn.addEventListener('click', () => {
                const row = list.querySelector('[data-repeater-row]').cloneNode(true);
                row.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                    input.name = input.name.replace(/specs\[\d+\]/, `specs[${index}]`);
                });
                index++;
                list.appendChild(row);
                row.querySelector('input').focus();
            });
        });
    </script>
@endsection
