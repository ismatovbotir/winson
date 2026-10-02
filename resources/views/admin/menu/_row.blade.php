@php $input = 'w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30'; @endphp
<div data-menu-row class="grid gap-2 rounded-lg border border-line p-3 lg:grid-cols-[auto_1fr_1fr_1.4fr_auto_auto_auto] lg:items-center lg:border-0 lg:p-0">
    <div class="flex w-14 gap-1">
        <button type="button" data-action="up" title="{{ __('admin.menu.up') }}" aria-label="{{ __('admin.menu.up') }}"
            class="grid h-8 w-6 place-items-center rounded text-ink-soft hover:bg-canvas-alt hover:text-navy">↑</button>
        <button type="button" data-action="down" title="{{ __('admin.menu.down') }}" aria-label="{{ __('admin.menu.down') }}"
            class="grid h-8 w-6 place-items-center rounded text-ink-soft hover:bg-canvas-alt hover:text-navy">↓</button>
    </div>
    <input type="text" name="items[{{ $i }}][label_uz]" value="{{ $row['label_uz'] ?? '' }}" placeholder="{{ __('admin.menu.label_uz') }}" class="{{ $input }}">
    <input type="text" name="items[{{ $i }}][label_ru]" value="{{ $row['label_ru'] ?? '' }}" placeholder="{{ __('admin.menu.label_ru') }}" class="{{ $input }}">
    <input type="text" name="items[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="/catalog" list="menu-suggestions" class="{{ $input }} font-mono">
    <label class="flex items-center gap-2 text-sm text-ink-soft lg:justify-center">
        <input type="checkbox" name="items[{{ $i }}][new_tab]" value="1" @checked($row['new_tab'] ?? false) class="rounded border-line">
        <span class="lg:sr-only">{{ __('admin.menu.new_tab') }}</span>
    </label>
    <label class="flex items-center gap-2 text-sm text-ink-soft lg:justify-center">
        <input type="checkbox" name="items[{{ $i }}][is_active]" value="1" @checked($row['is_active'] ?? true) class="rounded border-line">
        <span class="lg:sr-only">{{ __('admin.menu.is_active') }}</span>
    </label>
    <button type="button" data-action="remove" title="{{ __('admin.menu.remove') }}" aria-label="{{ __('admin.menu.remove') }}"
        class="grid h-9 w-8 place-items-center justify-self-end rounded-md text-lg text-ink-soft hover:bg-red-50 hover:text-red-600">×</button>
</div>
