{{-- Faceted filter panel (GET form). Counts = results you'd get by adding that option. --}}
@php $activeCount = count($chips); @endphp
<aside class="lg:sticky lg:top-28 lg:self-start">
    <details class="group/f rounded-xl border border-line bg-white lg:border-0 lg:bg-transparent" @if ($activeCount) open @endif data-filters-details>
        <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 font-semibold text-navy lg:hidden">
            <span>{{ __('site.filters.title') }} @if ($activeCount)<span class="ml-1 rounded-full bg-accent px-2 py-0.5 text-xs text-navy-deep">{{ $activeCount }}</span>@endif</span>
            <span aria-hidden="true" class="transition group-open/f:rotate-180">⌄</span>
        </summary>

        <form method="GET" action="{{ url()->current() }}" data-filters class="space-y-6 border-t border-line px-4 py-4 lg:max-h-[calc(100vh-8rem)] lg:overflow-y-auto lg:border-0 lg:p-0 lg:pr-2">
            <p class="hidden font-mono text-xs uppercase tracking-[0.14em] text-ink-soft lg:block">{{ __('site.filters.title') }}</p>

            @foreach ($facets as $group => $groupFacets)
                <div class="space-y-5">
                    <p class="font-mono text-[11px] uppercase tracking-[0.16em] text-accent-ink">{{ __('site.filters.groups.'.$group) }}</p>

                    @foreach ($groupFacets as $facet)
                        @php $f = $facet['feature']; $name = 'f['.$f->code.']'; @endphp
                        <fieldset class="border-b border-line pb-5 last:border-0">
                            <legend class="mb-2 text-sm font-semibold text-navy">
                                {{ $f->name }}@if ($f->unit && $facet['type'] === 'number') <span class="font-normal text-ink-soft">({{ $f->unit }})</span>@endif
                            </legend>

                            @if (isset($facet['options']))
                                <div class="space-y-1.5">
                                    @foreach ($facet['options'] as $opt)
                                        @php $disabled = $opt['count'] === 0 && ! $opt['selected']; @endphp
                                        <label @class(['flex items-center gap-2.5 text-sm', 'cursor-pointer text-ink' => ! $disabled, 'cursor-not-allowed text-ink-soft/50' => $disabled])>
                                            <input type="checkbox" name="{{ $name }}[]" value="{{ $opt['option']->code }}"
                                                @checked($opt['selected']) @disabled($disabled)
                                                class="h-4 w-4 rounded border-line text-accent-ink focus:ring-accent">
                                            <span class="flex-1">{{ $opt['option']->label }}</span>
                                            <span class="font-mono text-xs text-ink-soft">{{ $opt['count'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($facet['type'] === 'boolean')
                                <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink">
                                    <input type="checkbox" name="{{ $name }}" value="1" @checked($facet['selected'])
                                        class="h-4 w-4 rounded border-line text-accent-ink focus:ring-accent">
                                    <span class="flex-1">{{ __('site.filters.yes') }}</span>
                                    <span class="font-mono text-xs text-ink-soft">{{ $facet['count'] }}</span>
                                </label>
                            @else
                                <div class="flex items-center gap-2">
                                    <input type="number" step="any" name="{{ $name }}[min]" value="{{ $facet['value']['min'] ?? '' }}"
                                        placeholder="{{ __('site.filters.from') }} {{ \App\Models\Feature::number($facet['min']) }}" aria-label="{{ $f->name }} — {{ __('site.filters.from') }}"
                                        class="w-full min-w-0 rounded-md border border-line bg-white px-2.5 py-1.5 text-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">
                                    <span class="text-ink-soft">–</span>
                                    <input type="number" step="any" name="{{ $name }}[max]" value="{{ $facet['value']['max'] ?? '' }}"
                                        placeholder="{{ __('site.filters.to') }} {{ \App\Models\Feature::number($facet['max']) }}" aria-label="{{ $f->name }} — {{ __('site.filters.to') }}"
                                        class="w-full min-w-0 rounded-md border border-line bg-white px-2.5 py-1.5 text-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">
                                </div>
                            @endif
                        </fieldset>
                    @endforeach
                </div>
            @endforeach

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white transition hover:bg-navy-soft" data-filters-submit>
                    {{ __('site.filters.apply') }}
                </button>
                @if ($activeCount)
                    <a href="{{ $filter->resetUrl() }}" class="text-sm text-ink-soft hover:text-navy">{{ __('site.filters.reset') }}</a>
                @endif
            </div>
        </form>
    </details>
</aside>

<script>
    (() => {
        // Checkboxes apply instantly; number fields on change (Enter/blur). The
        // submit button stays for no-JS. Empty fields are dropped from the URL.
        const form = document.querySelector('[data-filters]');
        const submit = () => {
            form.querySelectorAll('input[type=number]').forEach((i) => { if (i.value === '') i.disabled = true; });
            form.submit();
        };
        form.addEventListener('change', submit);
        form.querySelector('[data-filters-submit]').classList.add('lg:hidden');

        // Desktop: the panel is always open.
        const details = document.querySelector('[data-filters-details]');
        if (window.matchMedia('(min-width: 1024px)').matches) details.open = true;
    })();
</script>
