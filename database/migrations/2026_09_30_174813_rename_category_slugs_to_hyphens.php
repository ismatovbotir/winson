<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SEO: hyphenated, real-word slugs (audit M1). Old URLs 301 to the new
     * ones via routes/web.php (see LEGACY_CATEGORY_SLUGS in LegacyRedirectController).
     */
    private const MAP = [
        'scan_engine' => 'scan-engine',
        'fix_mounted' => 'fixed-mount',
        'smart_terminal' => 'smart-terminal',
    ];

    public function up(): void
    {
        $this->apply(self::MAP);
    }

    public function down(): void
    {
        $this->apply(array_flip(self::MAP));
    }

    private function apply(array $map): void
    {
        foreach ($map as $from => $to) {
            DB::table('categories')->where('slug', $from)->update(['slug' => $to]);
            foreach (['categories', 'products'] as $table) {
                DB::table($table)->where('image', "images/products/{$from}.svg")->update(['image' => "images/products/{$to}.svg"]);
            }
        }
    }
};
