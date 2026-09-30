<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-row table (id=1) holding the editable text for the homepage's
     * hero/about/cta blocks, uz + ru side by side. Nav labels and other
     * short UI chrome stay in lang/*.php — this table is for admin-editable
     * page *content*, not interface strings.
     */
    public function up(): void
    {
        Schema::create('home_contents', function (Blueprint $table) {
            $table->id();

            $table->string('hero_kicker_uz')->nullable();
            $table->string('hero_kicker_ru')->nullable();
            $table->string('hero_title_uz')->nullable();
            $table->string('hero_title_ru')->nullable();
            $table->text('hero_subtitle_uz')->nullable();
            $table->text('hero_subtitle_ru')->nullable();
            $table->string('hero_cta_primary_uz')->nullable();
            $table->string('hero_cta_primary_ru')->nullable();
            $table->string('hero_cta_secondary_uz')->nullable();
            $table->string('hero_cta_secondary_ru')->nullable();

            $table->string('about_kicker_uz')->nullable();
            $table->string('about_kicker_ru')->nullable();
            $table->string('about_title_uz')->nullable();
            $table->string('about_title_ru')->nullable();
            $table->text('about_body_uz')->nullable();
            $table->text('about_body_ru')->nullable();

            foreach ([1, 2, 3, 4] as $i) {
                $table->string("about_stat{$i}_label_uz")->nullable();
                $table->string("about_stat{$i}_label_ru")->nullable();
                $table->string("about_stat{$i}_value_uz")->nullable();
                $table->string("about_stat{$i}_value_ru")->nullable();
            }

            $table->string('cta_title_uz')->nullable();
            $table->string('cta_title_ru')->nullable();
            $table->text('cta_body_uz')->nullable();
            $table->text('cta_body_ru')->nullable();
            $table->string('cta_button_uz')->nullable();
            $table->string('cta_button_ru')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_contents');
    }
};
