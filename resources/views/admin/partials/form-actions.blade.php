<div class="sticky bottom-0 -mx-4 mt-6 flex items-center gap-3 border-t border-line bg-canvas/95 px-4 py-4 backdrop-blur sm:mx-0 sm:rounded-b-xl sm:px-0">
    <button type="submit"
        class="rounded-md bg-navy px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-navy-soft">
        {{ __('admin.common.save') }}
    </button>
    @isset($cancel)
        <a href="{{ $cancel }}" class="text-sm font-medium text-ink-soft hover:text-navy">{{ __('admin.common.cancel') }}</a>
    @endisset
</div>
