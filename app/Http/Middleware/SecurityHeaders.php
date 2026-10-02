<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers for every Laravel-served response. (Files under
 * /storage are served by the web server directly — set the same headers there
 * in the nginx/Apache config.)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        if ($request->is('tg', 'tg/*')) {
            // Telegram Web (web.telegram.org) shows Mini Apps in an iframe.
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self' https://web.telegram.org https://*.telegram.org");
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        } else {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->is('admin', 'admin/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
