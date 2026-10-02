<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chatbot knowledge, taught by admins (/admin → Chatbot training).
     * kind = guide: scanner setting instructions (steps + programming-barcode
     *        images) — the bot sends them VERBATIM; the AI only picks which one.
     * kind = faq:   facts the AI may use in its own words.
     * Applies to all models (applies_to_all) or to the linked products.
     */
    public function up(): void
    {
        Schema::create('bot_knowledge', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10)->default('guide');
            $table->string('title_uz');
            $table->string('title_ru');
            $table->text('content_uz');
            $table->text('content_ru');
            $table->text('keywords')->nullable(); // example phrasings, one per line
            $table->boolean('applies_to_all')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('times_sent')->default(0);
            $table->timestamps();
        });

        Schema::create('bot_knowledge_product', function (Blueprint $table) {
            $table->foreignId('bot_knowledge_id')->constrained('bot_knowledge')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['bot_knowledge_id', 'product_id']);
        });

        Schema::create('bot_knowledge_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_knowledge_id')->constrained('bot_knowledge')->cascadeOnDelete();
            $table->string('path');
            $table->string('caption_uz')->nullable();
            $table->string('caption_ru')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_knowledge_images');
        Schema::dropIfExists('bot_knowledge_product');
        Schema::dropIfExists('bot_knowledge');
    }
};
