<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Homepage slider slides, admin-managed. Only `image` is required. */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('title_uz')->nullable();
            $table->string('title_ru')->nullable();
            $table->text('text_uz')->nullable();
            $table->text('text_ru')->nullable();
            $table->string('button_uz')->nullable();
            $table->string('button_ru')->nullable();
            $table->string('link')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
