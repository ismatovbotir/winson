<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** "Request a price" submissions (Telegram Mini App now; other sources later). */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20)->default('telegram');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('telegram_user_id')->nullable()->index();
            $table->string('telegram_username')->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
