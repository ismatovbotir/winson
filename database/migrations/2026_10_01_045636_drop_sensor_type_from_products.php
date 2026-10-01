<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * sensor_type is now the "sensor" Feature (feature_values). Any product
     * whose sensor isn't recorded there yet gets it copied before the drop.
     */
    public function up(): void
    {
        $feature = DB::table('features')->where('code', 'sensor')->first();
        if ($feature) {
            $options = DB::table('feature_options')->where('feature_id', $feature->id)->pluck('id', 'code');
            foreach (DB::table('products')->whereNotNull('sensor_type')->get(['id', 'sensor_type']) as $p) {
                $exists = DB::table('feature_values')->where('product_id', $p->id)->where('feature_id', $feature->id)->exists();
                if (! $exists && isset($options[$p->sensor_type])) {
                    DB::table('feature_values')->insert([
                        'product_id' => $p->id, 'feature_id' => $feature->id,
                        'feature_option_id' => $options[$p->sensor_type],
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }

        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('sensor_type'));
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->string('sensor_type')->nullable()->after('description_ru'));
    }
};
