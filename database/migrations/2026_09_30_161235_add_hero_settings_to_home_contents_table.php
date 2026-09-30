<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Hero button targets (not translatable) and the spark-animation switch. */
    public function up(): void
    {
        Schema::table('home_contents', function (Blueprint $table) {
            $table->string('hero_cta_primary_link')->nullable()->after('hero_cta_secondary_ru');
            $table->string('hero_cta_secondary_link')->nullable()->after('hero_cta_primary_link');
            $table->boolean('hero_sparks')->default(true)->after('hero_cta_secondary_link');
        });
    }

    public function down(): void
    {
        Schema::table('home_contents', function (Blueprint $table) {
            $table->dropColumn(['hero_cta_primary_link', 'hero_cta_secondary_link', 'hero_sparks']);
        });
    }
};
