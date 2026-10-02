<?php

namespace App\Http\Controllers;

use App\Models\TelegramClient;
use App\Models\TelegramConversation;
use App\Support\AiAssistant;
use App\Support\BotConversations;
use App\Support\Telegram;
use Illuminate\Http\Request;

/**
 * Bot webhook (registered by `php artisan telegram:setup`).
 *
 * Client flow:
 *   /start → saved + asked to share phone → registered
 *   question → conversation starts (or continues) → AI answer (scanners only,
 *              client's language, plain text) + persistent "End conversation" button
 *   "End conversation" / /end → summary → "Should a manager contact you?" [Yes|No]
 *              → Yes creates a lead → "Rate the help" ⭐1–5 → thanks + catalog
 *   silence > AI_CONVERSATION_TIMEOUT → closed by `telegram:close-idle` (no message)
 *
 * Telegram must send our secret header. We answer 200 at once; slow AI work
 * runs after the response.
 */
class TelegramWebhookController extends Controller
{
    /** Texts of the persistent "End conversation" button, both languages. */
    private const END_COMMANDS = ['/end', '/stop', '/yakunlash'];

    public function __construct(private BotConversations $conversations) {}

    public function __invoke(Request $request)
    {
        $secret = Telegram::webhookSecret();
        if (! $secret || ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            return response('forbidden', 403);
        }

        if ($callback = $request->input('callback_query')) {
            $this->callback($callback);

            return response('ok');
        }

        $message = $request->input('message');
        $from = $message['from'] ?? null;
        $chatId = $message['chat']['id'] ?? null;
        $text = trim((string) ($message['text'] ?? ''));
        if (! $from || ! $chatId || ($from['is_bot'] ?? false)) {
            return response('ok');
        }

        // Groups: only /id (to find the managers' chat id); everything else ignored.
        if (($message['chat']['type'] ?? '') !== 'private') {
            if (strtolower(strtok($text, ' @')) === '/id') {
                Telegram::api('sendMessage', ['chat_id' => $chatId, 'text' => "chat_id: {$chatId}"]);
            }

            return response('ok');
        }

        $client = TelegramClient::fromTelegram($from, (int) $chatId);
        if ($client->is_blocked) {
            return response('ok');
        }

        $locale = $this->locale($client, $text);
        $t = fn (string $key, array $replace = []) => __('site.tg.'.$key, $replace, $locale);

        // Registration: the client's own shared contact.
        if (isset($message['contact'])) {
            $this->register($client, $message['contact'], $chatId, $t, $locale);

            return response('ok');
        }

        $command = $text !== '' && $text[0] === '/' ? strtolower(strtok($text, ' @')) : null;

        if ($command === '/id') {
            Telegram::api('sendMessage', ['chat_id' => $chatId, 'text' => "chat_id: {$chatId}"]);

            return response('ok');
        }

        if (in_array($command, ['/start', '/catalog', '/katalog'], true)) {
            $client->isRegistered()
                ? $this->sendCatalogButton($chatId, $t, $locale, $t('welcome_back', ['name' => $client->first_name ?: '']))
                : $this->askForContact($chatId, $t, $t('welcome'));

            return response('ok');
        }

        if ($text === '') {
            Telegram::api('sendMessage', ['chat_id' => $chatId, 'text' => $t('text_only')]);

            return response('ok');
        }

        if (! $client->isRegistered()) {
            $this->askForContact($chatId, $t, $t('register_first'));

            return response('ok');
        }

        // END of conversation (button text in either language, or a command).
        if (in_array($command, self::END_COMMANDS, true) || in_array($text, [__('site.tg.end_button', [], 'uz'), __('site.tg.end_button', [], 'ru')], true)) {
            $this->endByClient($client, $chatId, $t, $locale);

            return response('ok');
        }

        // A question: START or continue the conversation.
        $todayAnswers = $client->messages()->where('role', 'assistant')->where('created_at', '>=', now()->startOfDay())->count();
        if ($todayAnswers >= config('services.ai.daily_limit', 30)) {
            Telegram::api('sendMessage', ['chat_id' => $chatId, 'text' => $t('limit')]);

            return response('ok');
        }

        $conversation = $this->conversations->current($client, $locale);
        $isFirstReply = ! $conversation->messages()->exists();
        $conversation->update(['last_message_at' => now()]);
        Telegram::api('sendChatAction', ['chat_id' => $chatId, 'action' => 'typing']);

        dispatch(function () use ($client, $conversation, $text, $chatId, $locale, $isFirstReply) {
            $reply = app(AiAssistant::class)->answer($client, $text, $conversation);
            // Stored after the call: answer() sends history + this question separately.
            $conversation->messages()->create(['telegram_client_id' => $client->id, 'role' => 'user', 'text' => $text]);

            $replyText = $reply?->text;
            if ($reply === null) {
                $replyText = __('site.tg.ai_unavailable', ['url' => route('tg.home', ['locale' => $locale])], $locale);
            } else {
                // History notes which guides were sent, so follow-ups have context.
                $note = $reply->guides->map(fn ($g) => '[sent guide: '.$g->title_ru.']')->implode("\n");
                $conversation->messages()->create(['telegram_client_id' => $client->id, 'role' => 'assistant', 'text' => trim($reply->text."\n".$note)]);
            }

            $keyboard = $isFirstReply ? ['reply_markup' => [
                // Persistent "End conversation" button for the rest of the conversation.
                'keyboard' => [[['text' => __('site.tg.end_button', [], $locale)]]],
                'resize_keyboard' => true,
                'is_persistent' => true,
            ]] : [];

            if ($replyText === '') {
                $replyText = __('site.tg.guide_intro', [], $locale);
            }
            Telegram::api('sendMessage', ['chat_id' => $chatId, 'text' => $replyText, 'disable_web_page_preview' => true] + $keyboard);
            foreach ($reply?->guides ?? [] as $guide) {
                Telegram::sendGuide($chatId, $guide->load('images'), $locale);
            }
        })->afterResponse();

        return response('ok');
    }

    // ---------------- conversation end ----------------

    private function endByClient(TelegramClient $client, $chatId, callable $t, string $locale): void
    {
        $conversation = $client->conversations()->whereNull('ended_at')->first();
        $hadQuestions = $conversation && $conversation->messages()->exists();

        // Remove the "End conversation" keyboard in any case.
        Telegram::api('sendMessage', ['chat_id' => $chatId, 'text' => $t('ended'), 'reply_markup' => ['remove_keyboard' => true]]);

        if (! $hadQuestions) {
            $conversation && $this->conversations->end($conversation, 'client');
            $this->sendCatalogButton($chatId, $t, $locale, $t('ask_anything'));

            return;
        }

        Telegram::api('sendMessage', [
            'chat_id' => $chatId,
            'text' => $t('ask_contact'),
            'reply_markup' => ['inline_keyboard' => [[
                ['text' => $t('contact_yes'), 'callback_data' => "c:{$conversation->id}:1"],
                ['text' => $t('contact_no'), 'callback_data' => "c:{$conversation->id}:0"],
            ]]],
        ]);

        // Closed now (a new question starts a new conversation); the AI summary is
        // written after the response — usually before the client taps a button.
        $this->conversations->end($conversation, 'client', summarize: false);
        $id = $conversation->id;
        dispatch(fn () => app(BotConversations::class)->summarize(TelegramConversation::findOrFail($id)))->afterResponse();
    }

    /** Inline buttons: c:{conversation}:{1|0} (manager contact), r:{conversation}:{1-5} (rating). */
    private function callback(array $callback): void
    {
        $data = (string) ($callback['data'] ?? '');
        $fromId = (int) ($callback['from']['id'] ?? 0);
        $message = $callback['message'] ?? [];
        $chatId = $message['chat']['id'] ?? null;

        Telegram::api('answerCallbackQuery', ['callback_query_id' => $callback['id'] ?? '']);

        if (! preg_match('/^([cr]):(\d+):(\d)$/', $data, $m) || ! $chatId) {
            return;
        }
        $conversation = TelegramConversation::with('client')->find((int) $m[2]);
        // Only the conversation's own client may answer its buttons.
        if (! $conversation || (int) $conversation->client->telegram_user_id !== $fromId) {
            return;
        }

        $locale = $conversation->locale ?: 'uz';
        $t = fn (string $key, array $replace = []) => __('site.tg.'.$key, $replace, $locale);

        // Buttons are single-use: remove them from the message that was tapped.
        Telegram::api('editMessageReplyMarkup', ['chat_id' => $chatId, 'message_id' => $message['message_id'] ?? 0, 'reply_markup' => ['inline_keyboard' => []]]);

        if ($m[1] === 'c') {
            if ($m[3] === '1') {
                $this->conversations->requestContact($conversation);
                Telegram::api('sendMessage', ['chat_id' => $chatId, 'text' => $t('contact_ok')]);
            } else {
                $conversation->update(['wants_contact' => false]);
            }

            if ($conversation->rating === null) {
                Telegram::api('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $t('ask_rating'),
                    'reply_markup' => ['inline_keyboard' => [array_map(
                        fn ($n) => ['text' => "⭐ {$n}", 'callback_data' => "r:{$conversation->id}:{$n}"],
                        [1, 2, 3, 4, 5]
                    )]],
                ]);
            }

            return;
        }

        $rating = max(1, min(5, (int) $m[3]));
        if ($conversation->rating === null) {
            $conversation->update(['rating' => $rating]);
        }
        $this->sendCatalogButton($chatId, $t, $locale, $t($rating >= 4 ? 'thanks_good' : 'thanks_bad'));
    }

    // ---------------- registration & helpers ----------------

    private function register(TelegramClient $client, array $contact, $chatId, callable $t, string $locale): void
    {
        // Only the user's OWN contact (the button guarantees it; a forwarded card doesn't).
        if ((int) ($contact['user_id'] ?? 0) !== (int) $client->telegram_user_id) {
            $this->askForContact($chatId, $t, $t('own_contact'));

            return;
        }

        $client->update([
            'phone' => '+'.ltrim((string) $contact['phone_number'], '+'),
            'registered_at' => $client->registered_at ?? now(),
        ]);

        Telegram::api('sendMessage', ['chat_id' => $chatId, 'text' => $t('registered'), 'reply_markup' => ['remove_keyboard' => true]]);
        $this->sendCatalogButton($chatId, $t, $locale, $t('ask_anything'));
    }

    private function askForContact($chatId, callable $t, string $text): void
    {
        Telegram::api('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => [[['text' => $t('share_phone'), 'request_contact' => true]]],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ],
        ]);
    }

    private function sendCatalogButton($chatId, callable $t, string $locale, string $text): void
    {
        Telegram::api('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => ['inline_keyboard' => [[[
                'text' => $t('open_catalog'),
                'web_app' => ['url' => route('tg.home', ['locale' => $locale])],
            ]]]],
        ]);
    }

    /** Language for bot texts: script of what they wrote; for commands, their Telegram app language. */
    private function locale(TelegramClient $client, string $text): string
    {
        if ($text !== '' && $text[0] !== '/') {
            if (in_array($text, [__('site.tg.end_button', [], 'ru')], true)) {
                return 'ru';
            }
            if (in_array($text, [__('site.tg.end_button', [], 'uz')], true)) {
                return 'uz';
            }

            return AiAssistant::guessLocale($text);
        }

        return str_starts_with((string) $client->language_code, 'ru') ? 'ru' : 'uz';
    }
}
