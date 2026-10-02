<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            TrackPageView::class,
            SecurityHeaders::class,
        ]);

        // Telegram endpoints are authenticated by Telegram itself (webhook secret /
        // signed initData), not by a browser session, so CSRF doesn't apply.
        $middleware->validateCsrfTokens(except: ['telegram/webhook', 'tg/lead']);

        $middleware->alias([
            'admin.auth' => AdminAuthenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
