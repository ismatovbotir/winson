<?php

namespace App\Support;

use App\Models\Feature;
use App\Models\Product;
use Illuminate\Support\Collection;

/** A product's characteristics as display rows, grouped by Feature::GROUPS. */
class FeatureTable
{
    /** @return Collection<string, Collection<int, array{0: Feature, 1: string}>> group => [[feature, formatted value]…] */
    public static function for(Product $product): Collection
    {
        $product->loadMissing('featureValues.feature', 'featureValues.option');

        $rows = $product->featureValues
            ->groupBy('feature_id')
            ->map(function ($values) {
                $feature = $values->first()->feature;
                $values = $values->sortBy(fn ($v) => $v->option?->sort_order);

                return [$feature, $feature->format($values)];
            })
            ->filter(fn ($row) => $row[1] !== null)
            ->sortBy(fn ($row) => $row[0]->sort_order);

        return collect(Feature::GROUPS)
            ->mapWithKeys(fn ($group) => [$group => $rows->filter(fn ($row) => $row[0]->group === $group)->values()])
            ->filter(fn ($rows) => $rows->isNotEmpty());
    }
}
