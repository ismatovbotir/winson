<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * This site only ships uz/ru content — no English.
     *
     * Public pages carry the locale in the URL (/uz/…, /ru/…): that wins, is
     * remembered in the session (so "/" sends returning visitors back to their
     * language), and is removed from the route parameters so controllers don't
     * receive it. Everything else (admin, sitemap, 404s) falls back to the
     * session, then the browser language, then config('app.default_locale').
     *
     * URL::defaults() lets route('catalog.index') etc. omit the locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['uz', 'ru']);
        $route = $request->route();
        $fromUrl = $route?->parameter('locale');

        if (in_array($fromUrl, $supported, true)) {
            $locale = $fromUrl;
            $route->forgetParameter('locale');
            $request->session()->put('locale', $locale);
        } else {
            $locale = $request->session()->get('locale')
                ?? $request->getPreferredLanguage($supported)
                ?? config('app.default_locale');
        }

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.default_locale');
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
