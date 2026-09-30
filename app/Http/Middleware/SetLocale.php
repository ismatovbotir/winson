<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * This site only ships uz/ru content — no English. Locale comes from the
     * session (set by the switcher route), falling back to the visitor's
     * browser language, falling back to config('app.locale').
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['uz', 'ru']);

        $locale = $request->session()->get('locale')
            ?? $request->getPreferredLanguage($supported)
            ?? config('app.locale');

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
