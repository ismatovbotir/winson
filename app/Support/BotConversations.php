<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\TelegramClient;
use App\Models\TelegramConversation;

/**
 * Conversation (session) lifecycle for the Telegram AI assistant.
 *
 * START: a registered client's first question, or the first one after the
 *        previous conversation ended / was idle longer than
 *        services.ai.conversation_timeout minutes.
 * END:   by the client ("End conversation" button / /end) → summary, offer a
 *        manager call (lead), ask for a 1–5 rating; or by inactivity
 *        (`telegram:close-idle`, scheduled) → summary only, managers are
 *        alerted if the AI rated the client's interest as high.
 */
class BotConversations
{
    public function __construct(private AiAssistant $ai) {}

    public static function timeoutMinutes(): int
    {
        return max(5, (int) config('services.ai.conversation_timeout', 30));
    }

    /** The client's open conversation, or a new one (closing an idle one first). */
    public function current(TelegramClient $client, string $locale): TelegramConversation
    {
        $open = $client->conversations()->whereNull('ended_at')->first();

        if ($open && $open->last_message_at->lt(now()->subMinutes(self::timeoutMinutes()))) {
            $this->end($open, 'timeout');
            $open = null;
        }

        return $open ?? $client->conversations()->create([
            'locale' => $locale,
            'started_at' => now(),
            'last_message_at' => now(),
        ]);
    }

    /**
     * Close (+ summarize unless $summarize = false, e.g. when the caller does it
     * after the HTTP response). Returns false if it was already closed (idempotent).
     */
    public function end(TelegramConversation $conversation, string $reason, bool $summarize = true): bool
    {
        $closed = TelegramConversation::whereKey($conversation->id)->whereNull('ended_at')
            ->update(['ended_at' => now(), 'end_reason' => $reason]);
        if (! $closed) {
            return false;
        }
        $conversation->refresh();

        // An empty conversation (no questions) leaves no trace.
        if (! $conversation->messages()->exists()) {
            $conversation->delete();

            return true;
        }

        if (! $summarize) {
            return true;
        }

        $this->summarize($conversation);

        if ($reason === 'timeout' && $conversation->interest === 'high' && ! $conversation->lead_id) {
            Telegram::notifyHotConversation($conversation);
        }

        return true;
    }

    public function summarize(TelegramConversation $conversation): void
    {
        if ($conversation->summary) {
            return;
        }

        $result = $this->ai->summarize($conversation);
        $conversation->update([
            'summary' => $result['summary'] ?? $this->transcriptExcerpt($conversation),
            'interest' => $result['interest'] ?? null,
        ]);
    }

    /** Client asked for a manager: create the lead (once) and alert managers. */
    public function requestContact(TelegramConversation $conversation): Lead
    {
        if ($conversation->lead) {
            return $conversation->lead;
        }

        $this->summarize($conversation);
        $client = $conversation->client;

        $lead = Lead::create([
            'source' => 'bot',
            'telegram_client_id' => $client->id,
            'name' => $client->displayName(),
            'phone' => $client->phone,
            'message' => trim(($conversation->interest ? '['.$conversation->interest.'] ' : '').$conversation->summary),
            'telegram_user_id' => $client->telegram_user_id,
            'telegram_username' => $client->username,
            'locale' => $conversation->locale,
        ]);

        $conversation->update(['wants_contact' => true, 'lead_id' => $lead->id]);
        Telegram::notifyLead($lead);

        return $lead;
    }

    /** Fallback summary when the AI is unavailable: the client's own last questions. */
    private function transcriptExcerpt(TelegramConversation $conversation): string
    {
        return $conversation->messages()->where('role', 'user')->latest('id')->take(5)->pluck('text')->reverse()
            ->map(fn ($t) => '• '.\Illuminate\Support\Str::limit($t, 200))->implode("\n");
    }
}
