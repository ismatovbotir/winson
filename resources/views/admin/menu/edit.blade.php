@extends('admin.layout')

@section('title', __('admin.menu.title'))

@php
    $rows = old('items', $items->map->only(['label_uz', 'label_ru', 'url', 'new_tab', 'is_active'])->all());
    $blank = ['label_uz' => '', 'label_ru' => '', 'url' => '', 'new_tab' => false, 'is_active' => true];
    $input = 'w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30';
@endphp

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.menu.title'),
        'subtitle' => __('admin.menu.subtitle'),
    ])

    <form method="POST" action="{{ route('admin.menu.update') }}">
        @csrf
        @method('PUT')

        <datalist id="menu-suggestions">
            @foreach ($suggestions as $s)
                <option value="{{ $s }}"></option>
            @endforeach
        </datalist>

        <x-admin.card>
            <div class="hidden gap-2 px-1 pb-2 text-xs font-medium uppercase tracking-wide text-ink-soft lg:grid lg:grid-cols-[auto_1fr_1fr_1.4fr_auto_auto_auto]">
                <span class="w-14"></span>
                <span>{{ __('admin.menu.label_uz') }}</span>
                <span>{{ __('admin.menu.label_ru') }}</span>
                <span>{{ __('admin.menu.url') }}</span>
                <span>{{ __('admin.menu.new_tab') }}</span>
                <span>{{ __('admin.menu.is_active') }}</span>
                <span class="w-8"></span>
            </div>

            <div data-menu class="space-y-3 lg:space-y-2">
                @foreach (array_values($rows) as $i => $row)
                    @include('admin.menu._row', ['i' => $i, 'row' => $row])
                @endforeach
            </div>

            <p data-menu-empty class="{{ count($rows) ? 'hidden' : '' }} py-4 text-sm text-ink-soft">{{ __('admin.menu.empty') }}</p>

            <template data-menu-template>
                @include('admin.menu._row', ['i' => '__i__', 'row' => $blank])
            </template>

            <button type="button" data-menu-add class="mt-4 text-sm font-semibold text-accent-ink hover:text-navy">{{ __('admin.menu.add') }}</button>
            <p class="mt-3 text-xs text-ink-soft">{{ __('admin.menu.url_hint') }}</p>
        </x-admin.card>

        @include('admin.partials.form-actions')
    </form>

    <script>
        (() => {
            const list = document.querySelector('[data-menu]');
            const template = document.querySelector('[data-menu-template]');
            const empty = document.querySelector('[data-menu-empty]');

            // Row order in the DOM is the saved order, so renumber names after any change.
            const renumber = () => {
                list.querySelectorAll('[data-menu-row]').forEach((row, i) => {
                    row.querySelectorAll('[name]').forEach((el) => {
                        el.name = el.name.replace(/items\[[^\]]+\]/, `items[${i}]`);
                    });
                });
                empty.classList.toggle('hidden', list.children.length > 0);
            };

            document.querySelector('[data-menu-add]').addEventListener('click', () => {
                list.insertAdjacentHTML('beforeend', template.innerHTML);
                renumber();
                list.lastElementChild.querySelector('input[type=text]').focus();
            });

            list.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;
                const row = btn.closest('[data-menu-row]');
                if (btn.dataset.action === 'remove') row.remove();
                if (btn.dataset.action === 'up' && row.previousElementSibling) row.after(row.previousElementSibling);
                if (btn.dataset.action === 'down' && row.nextElementSibling) row.before(row.nextElementSibling);
                renumber();
            });
        })();
    </script>
@endsection
