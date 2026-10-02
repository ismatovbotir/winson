<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bot clients (registered via /start + shared phone contact) and their
     * conversation with the AI assistant (context for follow-up questions,
     * and visible to admins in /admin → Telegram clients).
     */
    public function up(): void
    {
        Schema::create('telegram_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_user_id')->unique();
            $table->bigInteger('chat_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('language_code', 10)->nullable();
            $table->string('phone', 40)->nullable();
            $table->timestamp('registered_at')->nullable(); // set when the phone is shared
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });

        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_client_id')->constrained()->cascadeOnDelete();
            $table->string('role', 10); // user | assistant
            $table->text('text');
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('telegram_client_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', fn (Blueprint $t) => $t->dropConstrainedForeignId('telegram_client_id'));
        Schema::dropIfExists('telegram_messages');
        Schema::dropIfExists('telegram_clients');
    }
};
