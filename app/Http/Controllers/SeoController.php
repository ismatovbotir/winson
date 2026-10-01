<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Dynamic robots.txt and sitemap.xml (real domain from APP_URL, both
 * languages per page with hreflang). The sitemap is cached; Category,
 * Product and Article clear it on save/delete (see their booted()).
 */
class SeoController extends Controller
{
    public const SITEMAP_CACHE = 'seo.sitemap.xml';

    public function robots()
    {
        $lines = ['User-agent: *'];

        if (Setting::get('seo_indexing') === '0') {
            $lines[] = 'Disallow: /';
        } else {
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /til/';
            $lines[] = '';
            $lines[] = 'Sitemap: '.route('sitemap');
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap()
    {
        $xml = Cache::remember(self::SITEMAP_CACHE, now()->addHours(6), fn () => $this->buildSitemap());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function buildSitemap(): string
    {
        $locales = config('app.supported_locales', ['uz', 'ru']);
        $pages = [];

        // [route name, params, lastmod]
        $pages[] = ['home', [], null];
        $pages[] = ['catalog.index', [], Category::max('updated_at')];
        $pages[] = ['news.index', [], Article::max('updated_at')];

        foreach (Category::with('products')->orderBy('sort_order')->get() as $category) {
            $pages[] = ['catalog.category', [$category], $category->updated_at];
            foreach ($category->products as $product) {
                $pages[] = ['catalog.item', [$category, $product], $product->updated_at];
            }
        }
        foreach (Article::orderByDesc('published_at')->get() as $article) {
            $pages[] = ['news.show', [$article], $article->updated_at];
        }

        $out = ['<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'];

        foreach ($pages as [$name, $params, $lastmod]) {
            $urls = collect($locales)->mapWithKeys(fn ($l) => [$l => route($name, array_merge($params, ['locale' => $l]))]);

            foreach ($urls as $locale => $url) {
                $out[] = '  <url>';
                $out[] = '    <loc>'.e($url).'</loc>';
                if ($lastmod) {
                    $out[] = '    <lastmod>'.\Illuminate\Support\Carbon::parse($lastmod)->toAtomString().'</lastmod>';
                }
                foreach ($urls as $alt => $altUrl) {
                    $out[] = '    <xhtml:link rel="alternate" hreflang="'.$alt.'" href="'.e($altUrl).'"/>';
                }
                $out[] = '    <xhtml:link rel="alternate" hreflang="x-default" href="'.e($urls[config('app.default_locale')]).'"/>';
                $out[] = '  </url>';
            }
        }

        $out[] = '</urlset>';

        return implode("\n", $out)."\n";
    }
}
