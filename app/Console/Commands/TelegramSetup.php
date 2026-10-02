<?php

namespace App\Console\Commands;

use App\Support\Telegram;
use Illuminate\Console\Command;

/**
 * One-time (and after domain changes) bot setup: webhook, menu button that
 * opens the Mini App, bot commands. Needs TELEGRAM_BOT_TOKEN and a public
 * HTTPS APP_URL (Telegram can't reach localhost).
 */
class TelegramSetup extends Command
{
    protected $signature = 'telegram:setup {--info : Only show the current webhook status}';

    protected $description = 'Register the Telegram webhook, Mini App menu button and bot commands';

    public function handle(): int
    {
        if (! Telegram::configured()) {
            $this->error('TELEGRAM_BOT_TOKEN is not set in .env.');

            return self::FAILURE;
        }

        if ($this->option('info')) {
            $this->line(json_encode(Telegram::api('getWebhookInfo'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $webhook = route('telegram.webhook');
        $miniApp = route('tg.entry');
        if (! str_starts_with($webhook, 'https://')) {
            $this->error("APP_URL must be public HTTPS for Telegram (now: {$webhook}).");

            return self::FAILURE;
        }

        $steps = [
            'webhook' => Telegram::api('setWebhook', [
                'url' => $webhook,
                'secret_token' => Telegram::webhookSecret(),
                'allowed_updates' => ['message', 'callback_query'],
                'drop_pending_updates' => true,
            ]),
            'menu button' => Telegram::api('setChatMenuButton', [
                'menu_button' => ['type' => 'web_app', 'text' => 'Katalog', 'web_app' => ['url' => $miniApp]],
            ]),
            'commands (uz)' => Telegram::api('setMyCommands', ['commands' => [
                ['command' => 'start', 'description' => 'Boshlash / katalog'],
                ['command' => 'end', 'description' => 'Suhbatni yakunlash'],
            ]]),
            'commands (ru)' => Telegram::api('setMyCommands', ['language_code' => 'ru', 'commands' => [
                ['command' => 'start', 'description' => 'Начать / каталог'],
                ['command' => 'end', 'description' => 'Завершить разговор'],
            ]]),
        ];

        $failed = false;
        foreach ($steps as $name => $result) {
            $ok = (bool) ($result['ok'] ?? false);
            $failed = $failed || ! $ok;
            $ok ? $this->info("✓ {$name}") : $this->error("✗ {$name}: ".($result['description'] ?? 'no response'));
        }

        $this->line("Mini App URL: {$miniApp}");
        $this->line('Admin chat: '.(config('services.telegram.admin_chat_id') ?: 'not set — send /id to the bot in your admin chat, put it in TELEGRAM_ADMIN_CHAT_ID'));

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
