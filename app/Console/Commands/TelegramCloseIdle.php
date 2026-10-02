<?php

namespace App\Console\Commands;

use App\Models\TelegramConversation;
use App\Support\BotConversations;
use Illuminate\Console\Command;

/**
 * Scheduled (every 5 min): ends bot conversations idle longer than
 * AI_CONVERSATION_TIMEOUT — writes the manager summary and alerts managers
 * about high-interest clients. The client is NOT messaged (no spam).
 */
class TelegramCloseIdle extends Command
{
    protected $signature = 'telegram:close-idle';

    protected $description = 'End idle Telegram bot conversations (summary + hot-lead alerts)';

    public function handle(BotConversations $conversations): int
    {
        $idle = TelegramConversation::whereNull('ended_at')
            ->where('last_message_at', '<', now()->subMinutes(BotConversations::timeoutMinutes()))
            ->get();

        foreach ($idle as $conversation) {
            $conversations->end($conversation, 'timeout');
        }

        $this->info("Closed {$idle->count()} idle conversation(s).");

        return self::SUCCESS;
    }
}
