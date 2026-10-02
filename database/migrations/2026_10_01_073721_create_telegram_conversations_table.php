<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bot conversations (sessions). One starts with a client's first question
     * (or after the previous ended / went idle) and ends by the client
     * ("End conversation" button, /end) or by inactivity. On end the AI writes
     * a manager summary + interest level; the client may ask for a manager
     * call (→ lead) and rate the help 1–5.
     */
    public function up(): void
    {
        Schema::create('telegram_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_client_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('last_message_at');
            $table->timestamp('ended_at')->nullable()->index();
            $table->string('end_reason', 20)->nullable(); // client | timeout
            $table->text('summary')->nullable();
            $table->string('interest', 10)->nullable(); // high | medium | low
            $table->boolean('wants_contact')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->foreignId('telegram_conversation_id')->nullable()->after('telegram_client_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('telegram_messages', fn (Blueprint $t) => $t->dropConstrainedForeignId('telegram_conversation_id'));
        Schema::dropIfExists('telegram_conversations');
    }
};
