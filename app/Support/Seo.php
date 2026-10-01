<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Per-request SEO state. Page views fill it at the top of the template
 * (child views run before the layout), `partials/seo-head` renders it:
 * <title>, description, canonical, hreflang, Open Graph/Twitter, JSON-LD.
 *
 *   @php(\App\Support\Seo::page()->title(...)->description(...)->crumb(...))
 */
class Seo
{
    public const SITE_NAME = 'Winson';

    /** Social platforms can't render SVG; used when a page has no raster image. */
    public const DEFAULT_IMAGE = 'images/og-default.png';

    private ?string $title = null;

    private ?string $description = null;

    private string $type = 'website';

    private ?string $image = null;

    private bool $noindex = false;

    /** @var array<int, array{name: string, url: ?string}> */
    private array $crumbs = [];

    /** @var array<int, array<string, mixed>> extra JSON-LD nodes */
    private array $nodes = [];

    public static function page(): self
    {
        return app(self::class);
    }

    public function title(?string $title): self
    {
        $this->title = $title ? trim($title) : $this->title;

        return $this;
    }

    public function description(?string $text): self
    {
        if ($text) {
            $this->description = Str::limit(RichText::text($text), 160, '…');
        }

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /** Absolute or site-relative image URL; SVGs are ignored for social cards. */
    public function image(?string $url): self
    {
        if ($url && ! Str::endsWith(Str::lower(parse_url($url, PHP_URL_PATH) ?? ''), '.svg')) {
            $this->image = $url;
        }

        return $this;
    }

    public function noindex(): self
    {
        $this->noindex = true;

        return $this;
    }

    /** Breadcrumb step; the last one (current page) may omit the URL. */
    public function crumb(string $name, ?string $url = null): self
    {
        $this->crumbs[] = ['name' => $name, 'url' => $url];

        return $this;
    }

    /** Add a JSON-LD node (Product, Article, ItemList…) to the page graph. */
    public function node(array $node): self
    {
        $this->nodes[] = $node;

        return $this;
    }

    // ---- read side (partials/seo-head, partials/breadcrumbs) ----

    /** Full <title>: page title + brand, without doubling the brand. */
    public function fullTitle(): string
    {
        $title = $this->title ?: __('site.seo.home_title');

        return Str::contains($title, self::SITE_NAME) ? $title : $title.' — '.self::SITE_NAME;
    }

    public function metaDescription(): string
    {
        return $this->description ?: Str::limit(__('site.seo.home_description'), 160, '…');
    }

    public function isNoindex(): bool
    {
        return $this->noindex;
    }

    public function ogType(): string
    {
        return $this->type;
    }

    public function crumbs(): array
    {
        return $this->crumbs;
    }

    /** Same page in every language: ['uz' => url, 'ru' => url], or [] if not localized. */
    public function alternates(): array
    {
        $route = request()->route();
        if (! $route || ! $route->getName() || ! in_array('locale', $route->parameterNames(), true)) {
            return [];
        }

        $params = $route->originalParameters();

        return collect(config('app.supported_locales', ['uz', 'ru']))
            ->mapWithKeys(fn ($locale) => [$locale => route($route->getName(), array_merge($params, ['locale' => $locale]))])
            ->all();
    }

    /** Clean URL of this page (no query string). */
    public function canonical(): string
    {
        return $this->alternates()[app()->getLocale()] ?? url()->current();
    }

    public function imageUrl(): string
    {
        return $this->absolute($this->image ?: asset(self::DEFAULT_IMAGE));
    }

    public function ogLocale(string $locale): string
    {
        return $locale === 'ru' ? 'ru_RU' : 'uz_UZ';
    }

    /** The page's JSON-LD graph: Organization + WebSite + breadcrumbs + page nodes. */
    public function jsonLd(): string
    {
        $home = route('home');
        $orgId = url('/').'#organization';
        $contacts = Contacts::all();

        $sameAs = collect(preg_split('/\s+/', (string) Setting::get('seo_same_as')))
            ->push($contacts['telegram_url'])
            ->filter(fn ($u) => $u && preg_match('~^https?://~i', $u))
            ->unique()->values()->all();

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => self::SITE_NAME,
            'url' => $home,
            'logo' => asset('images/logo/winson-logo.png'),
            'email' => $contacts['email'],
            'telephone' => $contacts['phone'],
            'areaServed' => 'UZ',
            'sameAs' => $sameAs ?: null,
            'address' => Setting::get('contact_address_'.(app()->getLocale() === 'ru' ? 'ru' : 'uz'))
                ? ['@type' => 'PostalAddress', 'streetAddress' => $contacts['address'], 'addressCountry' => 'UZ']
                : null,
            'contactPoint' => ($contacts['phone'] || $contacts['email']) ? array_filter([
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'telephone' => $contacts['phone'],
                'email' => $contacts['email'],
                'areaServed' => 'UZ',
                'availableLanguage' => ['uz', 'ru'],
            ]) : null,
        ]);

        $graph = [
            $organization,
            [
                '@type' => 'WebSite',
                '@id' => url('/').'#website',
                'name' => self::SITE_NAME,
                'url' => $home,
                'inLanguage' => app()->getLocale(),
                'publisher' => ['@id' => $orgId],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('search.index').'?q={search_term_string}'],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ];

        if (count($this->crumbs) > 1) {
            $graph[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($this->crumbs)->values()->map(fn ($c, $i) => array_filter([
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $c['name'],
                    'item' => $c['url'] ? $this->absolute($c['url']) : null,
                ]))->all(),
            ];
        }

        foreach ($this->nodes as $node) {
            $graph[] = $node;
        }

        // json_encode with HEX_TAG/AMP escaping: admin text can't close the <script>.
        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT
        );
    }

    public function absolute(string $url): string
    {
        return preg_match('~^https?://~i', $url) ? $url : url($url);
    }

    /** Organization reference for other nodes (Article publisher etc.). */
    public static function organizationRef(): array
    {
        return ['@id' => url('/').'#organization'];
    }
}
