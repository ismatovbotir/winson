<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-editable SEO overrides. All optional: when empty, pages fall back
     * to their name/description/excerpt (see App\Support\Seo usage in views).
     * Categories also get an intro text (audit H4: category pages were thin).
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->text('description_uz')->nullable()->after('name_ru');
            $table->text('description_ru')->nullable()->after('description_uz');
        });

        foreach (['categories', 'products', 'articles'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('meta_title_uz')->nullable();
                $table->string('meta_title_ru')->nullable();
                $table->string('meta_description_uz', 500)->nullable();
                $table->string('meta_description_ru', 500)->nullable();
            });
        }

        Schema::table('home_contents', function (Blueprint $table) {
            $table->string('seo_title_uz')->nullable();
            $table->string('seo_title_ru')->nullable();
            $table->string('seo_description_uz', 500)->nullable();
            $table->string('seo_description_ru', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $t) => $t->dropColumn(['description_uz', 'description_ru']));
        foreach (['categories', 'products', 'articles'] as $name) {
            Schema::table($name, fn (Blueprint $t) => $t->dropColumn(['meta_title_uz', 'meta_title_ru', 'meta_description_uz', 'meta_description_ru']));
        }
        Schema::table('home_contents', fn (Blueprint $t) => $t->dropColumn(['seo_title_uz', 'seo_title_ru', 'seo_description_uz', 'seo_description_ru']));
    }
};
