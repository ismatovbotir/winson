<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/** The original hard-coded header links. Only seeds an empty menu. */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (MenuItem::exists()) {
            return;
        }

        $items = [
            ['nav.products', '/catalog'],
            ['nav.news', '/news'],
            ['nav.about', '/#about'],
            ['nav.contact', '/#contact'],
        ];

        foreach ($items as $i => [$key, $url]) {
            MenuItem::create([
                'label_uz' => __("site.{$key}", [], 'uz'),
                'label_ru' => __("site.{$key}", [], 'ru'),
                'url' => $url,
                'sort_order' => $i,
            ]);
        }
    }
}
