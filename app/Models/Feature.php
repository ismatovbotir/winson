<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A product characteristic definition (e.g. "Interfaces", "IP rating").
 * Values per product live in feature_values; see FeatureValue.
 */
class Feature extends Model
{
    public const TYPES = ['select', 'multi', 'boolean', 'number'];

    /** Display/filter groups, in order. Labels: lang admin.features.groups.* / site.filters.groups.* */
    public const GROUPS = ['scanning', 'connectivity', 'durability', 'power', 'device', 'other'];

    protected $fillable = ['code', 'group', 'type', 'name_uz', 'name_ru', 'unit_uz', 'unit_ru', 'is_filterable', 'sort_order'];

    protected $casts = ['is_filterable' => 'boolean'];

    protected static function booted(): void
    {
        // Feature names/options appear in product pages' JSON-LD; values in filters.
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\SeoController::SITEMAP_CACHE));
    }

    public function options(): HasMany
    {
        return $this->hasMany(FeatureOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(FeatureValue::class);
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, ['select', 'multi'], true);
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'ru' ? $this->name_ru : $this->name_uz);
    }

    protected function unit(): Attribute
    {
        return Attribute::get(fn () => (app()->getLocale() === 'ru' ? $this->unit_ru : $this->unit_uz) ?: null);
    }

    /** Human-readable value(s) of this feature for a set of value rows of one product. */
    public function format(iterable $values): ?string
    {
        $values = collect($values);
        if ($values->isEmpty()) {
            return null;
        }

        return match ($this->type) {
            'select', 'multi' => $values->map(fn ($v) => $v->option?->label)->filter()->implode(', ') ?: null,
            'boolean' => (float) $values->first()->value_number ? __('site.filters.yes') : __('site.filters.no'),
            'number' => self::number($values->first()->value_number).($this->unit ? ' '.$this->unit : ''),
        };
    }

    /** 1.50 → "1.5", 2.00 → "2" */
    public static function number($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
