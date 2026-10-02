<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    /*
    | Telegram bot + Mini App (see App\Support\Telegram, routes tg.*).
    | bot_token: from @BotFather. admin_chat_id: where new leads are sent
    | (send /id to the bot in that chat to learn it). webhook_secret: any
    | random string; Telegram echoes it back so we know updates are genuine.
    */
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
        'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],


    /*
    | AI for the Telegram bot assistant — any OpenAI-compatible chat API, so a
    | free provider can be used and swapped without code changes:
    |   Google Gemini (free tier): https://generativelanguage.googleapis.com/v1beta/openai  model gemini-2.0-flash
    |   Groq (free tier):          https://api.groq.com/openai/v1                           model llama-3.3-70b-versatile
    |   OpenRouter (free models):  https://openrouter.ai/api/v1                             model …:free
    |   Ollama (self-hosted):      http://localhost:11434/v1                                model qwen2.5:7b (no key)
    */
    'ai' => [
        'base_url' => env('AI_BASE_URL'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL'),
        'daily_limit' => (int) env('AI_DAILY_LIMIT', 30), // AI answers per client per day
        'timeout' => (int) env('AI_TIMEOUT', 30),
        // A bot conversation ends after this many minutes without messages.
        'conversation_timeout' => (int) env('AI_CONVERSATION_TIMEOUT', 30),
    ],

];
