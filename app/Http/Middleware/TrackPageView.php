<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Records public page views for the admin Statistics page. Runs after the
 * response is sent (terminate), so it never slows a page down. Skips admin
 * pages, logged-in admins browsing the site, bots, and non-200 responses.
 */
class TrackPageView
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget|python|headless|lighthouse/i';

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldTrack($request, $response)) {
            return;
        }

        try {
            $route = $request->route();
            $subject = $route?->parameter('product')
                ?? $route?->parameter('article')
                ?? $route?->parameter('category');

            PageView::create([
                'path' => mb_substr('/'.ltrim($request->path(), '/'), 0, 255),
                'route_name' => $route?->getName(),
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'locale' => app()->getLocale(),
                'visitor_hash' => hash('sha256', implode('|', [
                    $request->ip(), $request->userAgent(), now()->toDateString(), config('app.key'),
                ])),
                'referrer_host' => $this->externalReferrer($request),
            ]);
        } catch (Throwable $e) {
            report($e); // stats must never break the site
        }
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && ! $request->is('admin', 'admin/*', 'til/*', 'up')
            && ! $request->ajax()
            && ! $request->header('Purpose') && ! $request->header('Sec-Purpose') // link prefetch
            && ! Auth::check()
            && ! preg_match(self::BOT_PATTERN, (string) $request->userAgent());
    }

    private function externalReferrer(Request $request): ?string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return $host && $host !== $request->getHost() ? mb_substr($host, 0, 255) : null;
    }
}
