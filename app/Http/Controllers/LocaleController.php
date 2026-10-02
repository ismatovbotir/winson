<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Public URLs are locale-prefixed (/uz/…, /ru/…). These handle the entry
 * points without a prefix: the bare domain and pre-prefix legacy URLs.
 */
class LocaleController extends Controller
{
    /** Category slugs renamed for SEO (see the rename_category_slugs migration). */
    private const LEGACY_CATEGORY_SLUGS = [
        'scan_engine' => 'scan-engine',
        'fix_mounted' => 'fixed-mount',
        'smart_terminal' => 'smart-terminal',
    ];

    /** "/" → the visitor's language: last choice, else browser, else default. */
    public function root(Request $request)
    {
        $supported = config('app.supported_locales', ['uz', 'ru']);
        $locale = $request->session()->get('locale');
        if (! in_array($locale, $supported, true)) {
            $locale = $request->getPreferredLanguage($supported) ?? config('app.default_locale');
        }

        return redirect('/'.$locale, 302)->header('Vary', 'Accept-Language, Cookie');
    }

    /** Unprefixed URLs (/catalog/…, /katalog/…, /news/…) → permanent redirect to the /uz/ version. */
    public function legacy(Request $request, string $section, ?string $path = null)
    {
        return redirect(self::target(config('app.default_locale'), $section, $path), 301);
    }

    /** /uz/katalog/… and /uz/category/… (old section names) → /uz/catalog/…. */
    public function legacySection(string $old, ?string $path = null)
    {
        return redirect(self::target(app()->getLocale(), $old, $path), 301);
    }

    /** New URL for an old section + path: catalog sections become "catalog", old slugs are mapped. */
    private static function target(string $locale, string $section, ?string $path): string
    {
        $segments = $path === null || $path === '' ? [] : explode('/', trim($path, '/'));
        if ($section !== 'news') {
            $section = 'catalog';
            if (isset($segments[0])) {
                $segments[0] = self::LEGACY_CATEGORY_SLUGS[$segments[0]] ?? $segments[0];
            }
        }

        return '/'.$locale.'/'.implode('/', array_merge([$section], $segments));
    }

    /**
     * Old category slugs inside the new prefixed URLs. ({locale} is already
     * consumed by SetLocale, so it isn't a method argument here.)
     */
    public function legacyCategory(string $slug, ?string $rest = null)
    {
        return redirect('/'.app()->getLocale().'/catalog/'.self::LEGACY_CATEGORY_SLUGS[$slug].($rest ? '/'.$rest : ''), 301);
    }

    public static function legacySlugPattern(): string
    {
        return implode('|', array_keys(self::LEGACY_CATEGORY_SLUGS));
    }
}
