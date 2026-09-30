@props(['name' => 'image', 'label', 'url' => null, 'removable' => false])

<div class="flex flex-col gap-1.5">
    <label for="f_{{ $name }}" class="text-sm font-medium text-ink">{{ $label }}</label>
    <div class="flex min-w-0 flex-wrap items-center gap-4">
        @if ($url)
            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-md border border-line bg-canvas-alt p-1.5"
                title="{{ __('admin.common.current_image') }}">
                <img src="{{ $url }}" alt="" class="max-h-full max-w-full object-contain">
            </div>
        @endif
        <div class="flex min-w-0 flex-col gap-2">
            <input id="f_{{ $name }}" name="{{ $name }}" type="file" accept=".jpg,.jpeg,.png,.webp"
                class="min-w-0 max-w-full text-sm text-ink-soft file:mr-3 file:rounded-md file:border-0 file:bg-navy file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-navy-soft">
            @if ($removable && $url)
                <label class="flex items-center gap-2 text-sm text-red-600">
                    <input type="checkbox" name="remove_{{ $name }}" value="1" @checked(old('remove_'.$name)) class="rounded border-line">
                    {{ __('admin.common.remove_image') }}
                </label>
            @endif
        </div>
    </div>
    <p class="text-xs text-ink-soft">{{ __('admin.common.image_hint') }}</p>
    @error($name)
        <p class="text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
