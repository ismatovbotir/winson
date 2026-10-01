{{--
    Instant site search (opened from the header, "/" or Ctrl/⌘+K).
    Native <dialog>: focus trap, Esc and inert background for free.
    ARIA combobox → listbox; options are filled by resources/js/search.js
    from search.suggest JSON. Without JS the form still submits to search.index.
--}}
@php $popular = once(fn () => \App\Models\Category::orderBy('sort_order')->take(6)->get(['id', 'slug', 'name_uz', 'name_ru'])); @endphp
<dialog id="site-search" data-search
    data-suggest-url="{{ route('search.suggest') }}"
    data-msg-loading="{{ __('site.search.loading') }}"
    data-msg-none="{{ __('site.search.none_short') }}"
    data-msg-all="{{ __('site.search.see_all') }}"
    data-msg-error="{{ __('site.search.error') }}"
    aria-label="{{ __('site.search.title') }}"
    class="m-0 h-dvh max-h-none w-full max-w-none bg-transparent p-0 backdrop:bg-navy-deep/60 backdrop:backdrop-blur-sm sm:mx-auto sm:mt-[12vh] sm:h-auto sm:max-w-2xl sm:px-4">

    <div class="flex h-full flex-col overflow-hidden bg-white shadow-2xl sm:h-auto sm:max-h-[70vh] sm:rounded-2xl">
        <form action="{{ route('search.index') }}" method="GET" role="search" class="flex items-center gap-3 border-b border-line px-4 py-3" data-search-form>
            <svg class="h-5 w-5 shrink-0 text-accent-ink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
            <input type="search" name="q" data-search-input
                role="combobox" aria-expanded="false" aria-controls="search-listbox" aria-autocomplete="list"
                autocomplete="off" spellcheck="false" enterkeyhint="search" maxlength="100"
                placeholder="{{ __('site.search.placeholder') }}" aria-label="{{ __('site.search.title') }}"
                class="min-w-0 flex-1 border-0 bg-transparent py-1.5 text-base text-ink placeholder:text-ink-soft/70 focus:outline-none focus:ring-0 [&::-webkit-search-cancel-button]:hidden">
            <button type="button" data-search-clear hidden aria-label="{{ __('site.search.clear') }}"
                class="grid h-8 w-8 place-items-center rounded-md text-ink-soft hover:bg-canvas-alt hover:text-navy">×</button>
            <button type="button" data-search-close
                class="rounded-md border border-line px-2 py-1 font-mono text-xs text-ink-soft hover:border-accent hover:text-navy">
                <span class="hidden sm:inline">Esc</span><span class="sm:hidden">{{ __('site.search.close') }}</span>
            </button>
        </form>

        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain" data-search-body>
            {{-- Shown before typing --}}
            <div data-search-start class="space-y-6 p-5">
                <div data-search-recent hidden>
                    <div class="mb-2 flex items-center justify-between">
                        <p class="font-mono text-[11px] uppercase tracking-[0.16em] text-ink-soft">{{ __('site.search.recent') }}</p>
                        <button type="button" data-search-recent-clear class="text-xs text-ink-soft hover:text-navy">{{ __('site.search.recent_clear') }}</button>
                    </div>
                    <ul class="flex flex-wrap gap-2" data-search-recent-list></ul>
                </div>
                <div>
                    <p class="mb-2 font-mono text-[11px] uppercase tracking-[0.16em] text-ink-soft">{{ __('site.search.popular') }}</p>
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($popular as $category)
                            <li><a href="{{ route('catalog.category', $category) }}" class="inline-block rounded-full border border-line px-3 py-1.5 text-sm text-navy transition hover:border-accent hover:bg-accent-soft">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <p class="text-xs text-ink-soft">{{ __('site.search.hint') }}</p>
            </div>

            {{-- Live results --}}
            <ul id="search-listbox" role="listbox" aria-label="{{ __('site.search.results') }}" data-search-list hidden class="py-2"></ul>
            <p data-search-status role="status" aria-live="polite" class="px-5 py-6 text-center text-sm text-ink-soft" hidden></p>
        </div>

        <div class="hidden items-center gap-4 border-t border-line bg-canvas px-4 py-2 font-mono text-[11px] text-ink-soft sm:flex" aria-hidden="true">
            <span><kbd class="rounded border border-line bg-white px-1">↑</kbd> <kbd class="rounded border border-line bg-white px-1">↓</kbd> {{ __('site.search.kbd_move') }}</span>
            <span><kbd class="rounded border border-line bg-white px-1">Enter</kbd> {{ __('site.search.kbd_open') }}</span>
            <span><kbd class="rounded border border-line bg-white px-1">Esc</kbd> {{ __('site.search.kbd_close') }}</span>
        </div>
    </div>
</dialog>
