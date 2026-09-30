@props([
    'name',
    'label',
    'value' => null,
    'type' => 'text',
    'hint' => null,
    'rows' => null,
    'required' => false,
])

@php
    $id = 'f_'.str_replace(['[', ']', '.'], '_', $name);
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $current = old($errorKey, $value);
@endphp

<div {{ $attributes->class('flex flex-col gap-1.5') }}>
    <label for="{{ $id }}" class="text-sm font-medium text-ink">
        {{ $label }} @if ($required)<span class="text-accent-ink">*</span>@endif
    </label>

    @if ($rows)
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
            class="rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">{{ $current }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}" @required($required)
            class="rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">
    @endif

    @if ($hint)
        <p class="text-xs text-ink-soft">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p class="text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
