<?php

namespace App\Support;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Telegram Bot API + Mini App helpers. Token etc. come from .env via
 * config('services.telegram'); everything degrades gracefully (no exceptions
 * to visitors) when the bot isn't configured yet.
 */
class Telegram
{
    /** initData older than this is rejected (replay protection). */
    public const INIT_DATA_TTL = 86400;

    public static function token(): ?string
    {
        return config('services.telegram.bot_token') ?: null;
    }

    public static function configured(): bool
    {
        return (bool) self::token();
    }

    /** Secret Telegram must echo in X-Telegram-Bot-Api-Secret-Token (1–256 chars of A-Za-z0-9_-). */
    public static function webhookSecret(): ?string
    {
        $secret = config('services.telegram.webhook_secret');
        if ($secret) {
            return $secret;
        }

        // Not set in .env: derive a stable one from the token (still unguessable).
        return self::token() ? substr(hash('sha256', 'winson-webhook|'.self::token()), 0, 48) : null;
    }

    /** Call a Bot API method; returns the decoded response or null on failure (logged). */
    public static function api(string $method, array $params = []): ?array
    {
        if (! self::configured()) {
            return null;
        }

        try {
            $response = Http::timeout(10)->asJson()->post('https://api.telegram.org/bot'.self::token().'/'.$method, $params);
            $json = $response->json();
            if (! ($json['ok'] ?? false)) {
                Log::warning("Telegram {$method} failed", ['status' => $response->status(), 'description' => $json['description'] ?? null]);
            }

            return $json;
        } catch (Throwable $e) {
            Log::warning("Telegram {$method} error: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Validate Mini App initData (https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app):
     * secret = HMAC_SHA256("WebAppData", bot_token); hash = HMAC_SHA256(data_check_string, secret).
     * Returns the Telegram user array, or null if missing/forged/expired.
     */
    public static function validateInitData(?string $initData): ?array
    {
        if (! $initData || ! self::configured()) {
            return null;
        }

        parse_str($initData, $fields);
        $hash = $fields['hash'] ?? null;
        if (! is_string($hash)) {
            return null;
        }
        unset($fields['hash']);
        ksort($fields);

        $checkString = collect($fields)->map(fn ($v, $k) => "{$k}={$v}")->implode("\n");
        $secret = hash_hmac('sha256', self::token(), 'WebAppData', true);

        if (! hash_equals(hash_hmac('sha256', $checkString, $secret), $hash)) {
            return null;
        }
        if (now()->timestamp - (int) ($fields['auth_date'] ?? 0) > self::INIT_DATA_TTL) {
            return null;
        }

        $user = json_decode($fields['user'] ?? 'null', true);

        return is_array($user) && isset($user['id']) ? $user : null;
    }

    /**
     * Deliver a setting guide VERBATIM: title + steps, then its programming
     * barcodes (photo / albums of ≤10), uploaded from storage.
     */
    public static function sendGuide(int|string $chatId, \App\Models\BotKnowledge $guide, string $locale): void
    {
        self::api('sendMessage', [
            'chat_id' => $chatId,
            'text' => '🛠 '.$guide->title($locale)."\n\n".trim($guide->content($locale)),
            'disable_web_page_preview' => true,
        ]);

        $images = $guide->images->filter(fn ($i) => Storage::disk('public')->exists($i->path))->values();
        foreach ($images->chunk(10) as $chunk) {
            $chunk = $chunk->values();
            if ($chunk->count() === 1) {
                $img = $chunk[0];
                self::upload('sendPhoto', ['chat_id' => $chatId, 'caption' => $img->caption($locale)], ['photo' => $img->path]);

                continue;
            }
            $files = [];
            $media = [];
            foreach ($chunk as $i => $img) {
                $files["file{$i}"] = $img->path;
                $media[] = array_filter(['type' => 'photo', 'media' => "attach://file{$i}", 'caption' => $img->caption($locale)]);
            }
            self::upload('sendMediaGroup', ['chat_id' => $chatId, 'media' => json_encode($media, JSON_UNESCAPED_UNICODE)], $files);
        }

        $guide->increment('times_sent');
    }

    /** Multipart Bot API call; $files = [field => path on the public disk]. */
    private static function upload(string $method, array $params, array $files): ?array
    {
        if (! self::configured()) {
            return null;
        }

        try {
            $request = Http::timeout(30);
            foreach ($files as $field => $path) {
                $request = $request->attach($field, Storage::disk('public')->get($path), basename($path));
            }
            $json = $request->post('https://api.telegram.org/bot'.self::token().'/'.$method, array_filter($params, fn ($v) => $v !== null))->json();
            if (! ($json['ok'] ?? false)) {
                Log::warning("Telegram {$method} failed", ['description' => $json['description'] ?? null]);
            }

            return $json;
        } catch (Throwable $e) {
            Log::warning("Telegram {$method} error: ".$e->getMessage());

            return null;
        }
    }

    /** A conversation that ended by inactivity but looked like a real buyer. */
    public static function notifyHotConversation(\App\Models\TelegramConversation $conversation): void
    {
        $chat = config('services.telegram.admin_chat_id');
        if (! $chat) {
            return;
        }

        $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $client = $conversation->client;

        self::api('sendMessage', [
            'chat_id' => $chat,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'text' => implode("\n", array_filter([
                '🔥 <b>Qiziqqan mijoz / Заинтересованный клиент</b> (suhbat tugadi, aloqa so\'ramadi / не запросил связь)',
                '👤 '.$e($client->displayName()).($client->username ? ' (@'.$e($client->username).')' : ''),
                $client->phone ? '📞 '.$e($client->phone) : null,
                '📝 '.$e($conversation->summary),
                '✉️ <a href="tg://user?id='.(int) $client->telegram_user_id.'">Telegram</a> · <a href="'.$e(route('admin.telegram-clients.show', $client)).'">Admin</a>',
            ])),
        ]);
    }

    /** Send a new lead to the admin chat (HTML, everything user-provided escaped). */
    public static function notifyLead(Lead $lead): void
    {
        $chat = config('services.telegram.admin_chat_id');
        if (! $chat) {
            return;
        }

        $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $product = $lead->product;
        $lines = array_filter([
            $lead->source === 'bot' ? '🆕 <b>Bot: menejer so\'rovi / Запрос менеджера</b>' : '🆕 <b>Narx so\'rovi / Запрос цены</b>',
            $product ? '📦 <a href="'.$e($product->category ? route('catalog.item', [$product->category, $product, 'locale' => 'ru']) : '').'">'.$e($product->name_ru).'</a>' : null,
            '👤 '.$e($lead->name ?: '—').($lead->telegram_username ? ' (@'.$e($lead->telegram_username).')' : ''),
            $lead->phone ? '📞 '.$e($lead->phone) : null,
            $lead->message ? '💬 '.$e($lead->message) : null,
            $lead->telegram_user_id ? '✉️ <a href="tg://user?id='.(int) $lead->telegram_user_id.'">'.$e('Telegram')."</a>" : null,
            '🔗 <a href="'.$e(route('admin.leads.index')).'">Admin</a>',
        ]);

        self::api('sendMessage', [
            'chat_id' => $chat,
            'text' => implode("\n", $lines),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ]);
    }
}
