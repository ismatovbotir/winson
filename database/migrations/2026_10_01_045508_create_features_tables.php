<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Structured product characteristics used for the spec table and the
     * catalog filters (see App\Models\Feature, App\Support\ProductFilter).
     *
     * type: select (one option) | multi (several options) | boolean | number
     * A value row stores either feature_option_id (select/multi) or
     * value_number (number; boolean as 1/0).
     */
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('group', 40)->default('other');
            $table->string('type', 20);
            $table->string('name_uz');
            $table->string('name_ru');
            $table->string('unit_uz', 30)->nullable();
            $table->string('unit_ru', 30)->nullable();
            $table->boolean('is_filterable')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('feature_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('label_uz');
            $table->string('label_ru');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['feature_id', 'code']);
        });

        Schema::create('feature_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_option_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('value_number', 12, 2)->nullable();
            $table->timestamps();
            $table->index(['feature_id', 'feature_option_id']);
            $table->unique(['product_id', 'feature_id', 'feature_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_values');
        Schema::dropIfExists('feature_options');
        Schema::dropIfExists('features');
    }
};
