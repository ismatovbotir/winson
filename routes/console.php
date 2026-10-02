<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Remove article-editor uploads no article uses (needs the scheduler cron on the server).
Schedule::command('articles:prune-images')->daily();

// End idle Telegram bot conversations: manager summary + hot-lead alerts.
Schedule::command('telegram:close-idle')->everyFiveMinutes()->withoutOverlapping();
