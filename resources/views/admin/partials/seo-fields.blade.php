{{--
    SEO card for any model with meta_title_{uz,ru} / meta_description_{uz,ru}
    (or other column names via $titleField / $descriptionField).
    $fallback = ['uz' => ['title' => …, 'description' => …], 'ru' => …] is
    what the page uses when a field is left empty — shown as the placeholder
    and in the live Google preview. $path = page path shown in the preview.
--}}
@php
    $titleField ??= 'meta_title';
    $descriptionField ??= 'meta_description';
    $fallback ??= [];
    $path ??= '';
    $host = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();
    $input = 'w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30';
@endphp

<x-admin.card :title="__('admin.seo.card_title')">
    <p class="-mt-3 mb-5 text-xs text-ink-soft">{{ __('admin.seo.card_hint') }}</p>

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach (['uz', 'ru'] as $lang)
            @php
                $t = $titleField.'_'.$lang;
                $d = $descriptionField.'_'.$lang;
                $fbTitle = $fallback[$lang]['title'] ?? '';
                $fbDesc = \Illuminate\Support\Str::limit(\App\Support\RichText::text($fallback[$lang]['description'] ?? ''), 160, '…');
            @endphp
            <div data-seo-preview class="min-w-0 space-y-4">
                <p class="font-mono text-xs uppercase tracking-wide text-ink-soft">{{ __('admin.common.lang_'.$lang) }}</p>

                <div class="flex flex-col gap-1.5">
                    <label for="f_{{ $t }}" class="flex justify-between text-sm font-medium text-ink">
                        {{ __('admin.seo.meta_title') }}
                        <span data-count="60" class="font-mono text-xs text-ink-soft"></span>
                    </label>
                    <input id="f_{{ $t }}" name="{{ $t }}" type="text" maxlength="255" value="{{ old($t, $model->{$t}) }}"
                        placeholder="{{ $fbTitle }}" data-seo-title data-fallback="{{ $fbTitle }}" class="{{ $input }}">
                    @error($t) <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="f_{{ $d }}" class="flex justify-between text-sm font-medium text-ink">
                        {{ __('admin.seo.meta_description') }}
                        <span data-count="160" class="font-mono text-xs text-ink-soft"></span>
                    </label>
                    <textarea id="f_{{ $d }}" name="{{ $d }}" rows="3" maxlength="500"
                        placeholder="{{ $fbDesc }}" data-seo-desc data-fallback="{{ $fbDesc }}" class="{{ $input }}">{{ old($d, $model->{$d}) }}</textarea>
                    @error($d) <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Google-style snippet preview --}}
                <div class="rounded-lg border border-line bg-white p-4 font-[Arial,sans-serif]" aria-hidden="true">
                    <p class="truncate text-xs text-[#202124]">{{ $host }} › {{ $lang }}{{ $path ? ' › '.trim(str_replace('/', ' › ', $path), ' ›') : '' }}</p>
                    <p data-preview-title class="mt-1 truncate text-lg leading-snug text-[#1a0dab]"></p>
                    <p data-preview-desc class="mt-1 line-clamp-2 text-sm leading-snug text-[#4d5156]"></p>
                </div>
            </div>
        @endforeach
    </div>
</x-admin.card>

@once
    <script>
        (() => {
            const brand = ' — Winson';
            document.querySelectorAll('[data-seo-preview]').forEach((box) => {
                const title = box.querySelector('[data-seo-title]');
                const desc = box.querySelector('[data-seo-desc]');
                const pTitle = box.querySelector('[data-preview-title]');
                const pDesc = box.querySelector('[data-preview-desc]');

                const count = (field) => {
                    const badge = field.closest('.flex-col').querySelector('[data-count]');
                    const limit = +badge.dataset.count;
                    const len = field.value.length;
                    badge.textContent = len ? `${len} / ${limit}` : '';
                    badge.classList.toggle('text-red-600', len > limit);
                    badge.classList.toggle('text-ink-soft', len <= limit);
                };
                const update = () => {
                    const t = title.value.trim() || title.dataset.fallback;
                    pTitle.textContent = t.includes('Winson') ? t : t + brand;
                    const d = desc.value.trim() || desc.dataset.fallback;
                    pDesc.textContent = d.length > 160 ? d.slice(0, 157) + '…' : d;
                    count(title);
                    count(desc);
                };
                title.addEventListener('input', update);
                desc.addEventListener('input', update);
                update();
            });
        })();
    </script>
@endonce
