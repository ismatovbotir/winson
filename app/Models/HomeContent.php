<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-row (id=1) editable content for the homepage hero/about/cta
 * sections. Use HomeContent::current() to fetch (creating defaults on first
 * access) and ->t('hero_title') to read the current-locale value of a field.
 */
class HomeContent extends Model
{
    protected $fillable = [
        'hero_kicker_uz', 'hero_kicker_ru',
        'hero_title_uz', 'hero_title_ru',
        'hero_subtitle_uz', 'hero_subtitle_ru',
        'hero_cta_primary_uz', 'hero_cta_primary_ru',
        'hero_cta_secondary_uz', 'hero_cta_secondary_ru',
        'hero_cta_primary_link', 'hero_cta_secondary_link', 'hero_sparks',
        'about_kicker_uz', 'about_kicker_ru',
        'about_title_uz', 'about_title_ru',
        'about_body_uz', 'about_body_ru',
        'about_stat1_label_uz', 'about_stat1_label_ru', 'about_stat1_value_uz', 'about_stat1_value_ru',
        'about_stat2_label_uz', 'about_stat2_label_ru', 'about_stat2_value_uz', 'about_stat2_value_ru',
        'about_stat3_label_uz', 'about_stat3_label_ru', 'about_stat3_value_uz', 'about_stat3_value_ru',
        'about_stat4_label_uz', 'about_stat4_label_ru', 'about_stat4_value_uz', 'about_stat4_value_ru',
        'cta_title_uz', 'cta_title_ru',
        'cta_body_uz', 'cta_body_ru',
        'cta_button_uz', 'cta_button_ru',
    ];

    protected $casts = ['hero_sparks' => 'boolean'];

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public function t(string $field): ?string
    {
        $locale = app()->getLocale() === 'ru' ? 'ru' : 'uz';

        return $this->getAttribute("{$field}_{$locale}");
    }
}
