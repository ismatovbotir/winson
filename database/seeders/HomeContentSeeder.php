<?php

namespace Database\Seeders;

use App\Models\HomeContent;
use Illuminate\Database\Seeder;

/**
 * Seeds the single HomeContent row from the original lang/{uz,ru}/site.php
 * hero/about/cta strings, so the homepage looks identical before anyone
 * edits it in /admin.
 */
class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'hero_kicker' => 'site.hero.kicker',
            'hero_title' => 'site.hero.title',
            'hero_subtitle' => 'site.hero.subtitle',
            'hero_cta_primary' => 'site.hero.cta_primary',
            'hero_cta_secondary' => 'site.hero.cta_secondary',
            'about_kicker' => 'site.about.kicker',
            'about_title' => 'site.about.title',
            'about_body' => 'site.about.body',
            'about_stat1_label' => 'site.about.stat_lines_label',
            'about_stat1_value' => 'site.about.stat_lines_value',
            'about_stat2_label' => 'site.about.stat_years_label',
            'about_stat2_value' => 'site.about.stat_years_value',
            'about_stat3_label' => 'site.about.stat_sensors_label',
            'about_stat3_value' => 'site.about.stat_sensors_value',
            'about_stat4_label' => 'site.about.stat_oem_label',
            'about_stat4_value' => 'site.about.stat_oem_value',
            'cta_title' => 'site.cta.title',
            'cta_body' => 'site.cta.body',
            'cta_button' => 'site.cta.button',
        ];

        $values = [];
        foreach ($map as $field => $key) {
            foreach (['uz', 'ru'] as $locale) {
                $values["{$field}_{$locale}"] = __($key, [], $locale);
            }
        }

        HomeContent::query()->updateOrCreate(['id' => 1], $values);
    }
}
