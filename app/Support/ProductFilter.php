<?php

namespace App\Support;

use App\Models\Feature;
use Illuminate\Support\Collection;

/**
 * Faceted filtering of a product list by Feature values.
 *
 * Query format (GET):  ?f[interfaces][]=usb_hid&f[interfaces][]=rs232   (options: OR within a feature)
 *                      ?f[reads_screens]=1                               (boolean: must be "yes")
 *                      ?f[scan_rate][min]=100&f[scan_rate][max]=300      (number range)
 * Different features combine with AND.
 *
 * Facet counts are "what you'd get if you added this": for feature F they're
 * computed over products matching every active filter EXCEPT F's own, so
 * options inside one group stay selectable together.
 *
 * Works in memory — a category holds tens of products, not thousands.
 */
class ProductFilter
{
    /** @var Collection<int, Feature> filterable features that occur in this product set */
    private Collection $features;

    /** product_id => feature_id => ['options' => [code…], 'number' => ?float] */
    private array $index = [];

    /** feature code => ['options' => [code…]] | ['yes' => true] | ['min' => ?float, 'max' => ?float] */
    private array $selected = [];

    public function __construct(private Collection $products, array $input)
    {
        $products->loadMissing('featureValues.option');

        foreach ($products as $product) {
            foreach ($product->featureValues as $value) {
                $row = &$this->index[$product->id][$value->feature_id];
                $row ??= ['options' => [], 'number' => null];
                if ($value->option) {
                    $row['options'][] = $value->option->code;
                } elseif ($value->value_number !== null) {
                    $row['number'] = (float) $value->value_number;
                }
                unset($row);
            }
        }

        $usedFeatureIds = collect($this->index)->flatMap(fn ($byFeature) => array_keys($byFeature))->unique();

        $this->features = Feature::with('options')
            ->where('is_filterable', true)
            ->whereIn('id', $usedFeatureIds)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('code');

        $this->selected = $this->parse($input);
    }

    public function results(): Collection
    {
        return $this->products->filter(fn ($p) => $this->matches($p->id))->values();
    }

    public function hasActive(): bool
    {
        return $this->selected !== [];
    }

    /**
     * Filter panel data, grouped by Feature::GROUPS.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function facets(): array
    {
        $facets = [];

        foreach ($this->features as $code => $feature) {
            $pool = $this->products->filter(fn ($p) => $this->matches($p->id, except: $code));
            $sel = $this->selected[$code] ?? null;
            $facet = ['feature' => $feature, 'type' => $feature->type];

            if ($feature->hasOptions()) {
                $present = collect($this->index)->flatMap(fn ($row) => $row[$feature->id]['options'] ?? [])->unique();
                $facet['options'] = $feature->options
                    ->filter(fn ($o) => $present->contains($o->code))
                    ->map(fn ($o) => [
                        'option' => $o,
                        'count' => $pool->filter(fn ($p) => in_array($o->code, $this->index[$p->id][$feature->id]['options'] ?? [], true))->count(),
                        'selected' => in_array($o->code, $sel['options'] ?? [], true),
                    ])->values()->all();
                if (! $facet['options']) {
                    continue;
                }
            } elseif ($feature->type === 'boolean') {
                $facet['count'] = $pool->filter(fn ($p) => ($this->index[$p->id][$feature->id]['number'] ?? 0) == 1)->count();
                $facet['selected'] = (bool) $sel;
                if ($facet['count'] === 0 && ! $facet['selected']) {
                    continue;
                }
            } else { // number
                $numbers = $this->products->map(fn ($p) => $this->index[$p->id][$feature->id]['number'] ?? null)->filter(fn ($n) => $n !== null);
                if ($numbers->count() < 2 && ! $sel) {
                    continue; // a range over one product is useless
                }
                $facet['min'] = $numbers->min();
                $facet['max'] = $numbers->max();
                $facet['value'] = $sel ?? ['min' => null, 'max' => null];
            }

            $facets[$feature->group][] = $facet;
        }

        return collect(Feature::GROUPS)->filter(fn ($g) => isset($facets[$g]))->mapWithKeys(fn ($g) => [$g => $facets[$g]])->all();
    }

    /** Active filters as removable chips: [['label' => …, 'url' => …], …] */
    public function chips(): array
    {
        $chips = [];
        foreach ($this->selected as $code => $sel) {
            $feature = $this->features[$code];
            if (isset($sel['options'])) {
                foreach ($sel['options'] as $optionCode) {
                    $chips[] = [
                        'label' => $feature->options->firstWhere('code', $optionCode)?->label,
                        'url' => $this->url($this->without($code, $optionCode)),
                    ];
                }
            } elseif (isset($sel['yes'])) {
                $chips[] = ['label' => $feature->name, 'url' => $this->url($this->without($code))];
            } else {
                $range = collect([$sel['min'] !== null ? '≥ '.Feature::number($sel['min']) : null, $sel['max'] !== null ? '≤ '.Feature::number($sel['max']) : null])->filter()->implode(' ');
                $chips[] = ['label' => $feature->name.': '.$range.($feature->unit ? ' '.$feature->unit : ''), 'url' => $this->url($this->without($code))];
            }
        }

        return $chips;
    }

    public function resetUrl(): string
    {
        return $this->url([]);
    }

    // ---- internals ----

    private function matches(int $productId, ?string $except = null): bool
    {
        foreach ($this->selected as $code => $sel) {
            if ($code === $except) {
                continue;
            }
            $row = $this->index[$productId][$this->features[$code]->id] ?? null;

            if (isset($sel['options'])) {
                if (! $row || ! array_intersect($sel['options'], $row['options'])) {
                    return false;
                }
            } elseif (isset($sel['yes'])) {
                if (($row['number'] ?? 0) != 1) {
                    return false;
                }
            } else {
                $n = $row['number'] ?? null;
                if ($n === null || ($sel['min'] !== null && $n < $sel['min']) || ($sel['max'] !== null && $n > $sel['max'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /** Keep only known features/options and numeric bounds; drop everything else. */
    private function parse(array $input): array
    {
        $selected = [];
        foreach ($input as $code => $raw) {
            $feature = $this->features[$code] ?? null;
            if (! $feature) {
                continue;
            }

            if ($feature->hasOptions()) {
                $valid = $feature->options->pluck('code')->all();
                $codes = array_values(array_unique(array_intersect(array_map('strval', (array) $raw), $valid)));
                if ($codes) {
                    $selected[$code] = ['options' => $codes];
                }
            } elseif ($feature->type === 'boolean') {
                if ($raw === '1' || $raw === 1) {
                    $selected[$code] = ['yes' => true];
                }
            } elseif (is_array($raw)) {
                $min = isset($raw['min']) && is_numeric($raw['min']) ? (float) $raw['min'] : null;
                $max = isset($raw['max']) && is_numeric($raw['max']) ? (float) $raw['max'] : null;
                if ($min !== null || $max !== null) {
                    $selected[$code] = ['min' => $min, 'max' => $max];
                }
            }
        }

        return $selected;
    }

    /** The current selection minus one feature (or one of its options), as query input. */
    private function without(string $code, ?string $optionCode = null): array
    {
        $query = $this->toQuery();
        if ($optionCode !== null) {
            $query[$code] = array_values(array_diff($query[$code] ?? [], [$optionCode]));
            if (! $query[$code]) {
                unset($query[$code]);
            }
        } else {
            unset($query[$code]);
        }

        return $query;
    }

    private function toQuery(): array
    {
        return collect($this->selected)->map(fn ($sel) => match (true) {
            isset($sel['options']) => $sel['options'],
            isset($sel['yes']) => '1',
            default => array_filter(['min' => $sel['min'], 'max' => $sel['max']], fn ($v) => $v !== null),
        })->all();
    }

    private function url(array $query): string
    {
        return url()->current().($query ? '?'.http_build_query(['f' => $query]) : '');
    }
}
