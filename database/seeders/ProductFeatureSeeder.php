<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Characteristics of the demo catalog — ONLY facts already stated in each
 * product's seeded description / sensor type (wireless, IP65, Android…).
 * Everything else (scan rate, interfaces, battery…) must come from the real
 * Winson datasheets via /admin; never fill it with guesses.
 *
 * value: option code | [option codes] | bool | number
 */
class ProductFeatureSeeder extends Seeder
{
    private const VALUES = [
        'wnl-7000g' => ['sensor' => 'laser', 'code_dimension' => '1d', 'connection' => 'wireless', 'mounting' => ['handheld']],
        'wni-9610' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'connection' => 'wired', 'reads_damaged' => true, 'mounting' => ['handheld']],
        'st10-71' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'connection' => 'wireless', 'wireless' => ['bluetooth'], 'cradle' => true, 'mounting' => ['handheld']],
        'st10-70-ip65' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'connection' => 'wired', 'ip_rating' => 'ip65', 'mounting' => ['handheld']],
        'wai-6770' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'scan_modes' => ['auto_sense'], 'mounting' => ['desktop']],
        'wai-6780' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'scan_pattern' => 'multi_line', 'scan_modes' => ['auto_sense'], 'mounting' => ['desktop']],
        'wdc-3000' => ['sensor' => 'laser', 'code_dimension' => '1d', 'mounting' => ['panel']],
        'wdi3038' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'mounting' => ['panel']],
        'wgl-1010' => ['sensor' => 'laser', 'code_dimension' => '1d', 'mounting' => ['fixed']],
        'wgi-3220' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'reads_screens' => true, 'mounting' => ['fixed']],
        'z2-670' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'mounting' => ['panel']],
        'z4p-520' => ['sensor' => 'cmos', 'code_dimension' => '1d2d', 'mounting' => ['panel']],
        'winny-pro' => ['sensor' => 'cmos', 'ip_rating' => 'ip54', 'mounting' => ['handheld']],
        'wpc-9082hc' => ['sensor' => 'cmos', 'os' => 'android', 'mounting' => ['handheld']],
        'kiosk-15-6-pos' => ['screen_size' => 15.6, 'touchscreen' => true, 'printer' => true],
        'price-checker-win10' => ['sensor' => 'cmos', 'os' => 'windows', 'touchscreen' => true],
    ];

    public function run(): void
    {
        $features = Feature::with('options')->get()->keyBy('code');

        foreach (self::VALUES as $slug => $values) {
            $product = Product::where('slug', $slug)->first();
            if (! $product) {
                continue;
            }

            foreach ($values as $code => $value) {
                $feature = $features[$code];
                $product->featureValues()->where('feature_id', $feature->id)->delete();

                if ($feature->hasOptions()) {
                    foreach ((array) $value as $optionCode) {
                        $product->featureValues()->create([
                            'feature_id' => $feature->id,
                            'feature_option_id' => $feature->options->firstWhere('code', $optionCode)->id,
                        ]);
                    }
                } else {
                    $product->featureValues()->create([
                        'feature_id' => $feature->id,
                        'value_number' => is_bool($value) ? (int) $value : $value,
                    ]);
                }
            }
        }
    }
}
