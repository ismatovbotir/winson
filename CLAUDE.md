# Winson

## Project idea

A bilingual (Uzbek/Russian, **no English**) public catalog/lead-gen site for
Winson barcode scanners and smart terminals, plus a small self-service CMS so
a non-technical small team (<5 people) can run it without a developer:

- **Public site**: home (hero/about/cta), a real product catalog
  (categories → items, each item with a name, description, technical
  parameters, an image gallery, and curated "similar items"), and a News/blog
  section (auto-ID and barcode-technology articles). It's informational, not
  transactional — every product funnels into a "contact us for pricing"
  CTA (email/WhatsApp/Telegram); there is no cart, checkout, or payment flow,
  and no price field anywhere in the schema.
- **Admin CMS** at `/admin` (single hardcoded admin login, no roles): full
  CRUD for categories, products (incl. parameters, image gallery, related
  items), and news articles, plus an editor for the homepage's hero/about/cta
  text blocks and a Settings page for Google Analytics / Yandex Metrica /
  Google Search Console / Yandex Webmaster fields. Hand-built Blade + plain
  server-rendered forms (small vanilla-JS repeaters where needed) — no
  Filament, no Livewire pulled in just for this; see "Admin CMS" below.
- **SEO**: the site is meant to actually rank for uz/ru searches, so it needs
  locale-prefixed URLs (`/uz/...`, `/ru/...`) with proper `hreflang`, per-page
  titles/descriptions, Open Graph/JSON-LD, and a sitemap — see "SEO" below for
  what's agreed vs. already built.
- Visual identity and structural inspiration come from the real
  winsonchina.com (see `.claude/winsonchina-reference.md`), but the actual
  theme/colors are original, derived from the real Winson logo, not copied
  from that site's (generic Shopify-style) design.

This section is the durable "what are we building" reference — the sections
below are the current implementation detail and may describe things still
in progress; check git history / actual files for exact current state.

## Team & scope

Maintained solo / by a small team (<5 people). No enforced testing or style
conventions yet (default PHPUnit + Pint are present but not mandated) — keep
changes simple and match existing patterns rather than introducing new
tooling or process unprompted.

## Stack

- Laravel 13, PHP 8.3+
- Blade views, with Livewire intended for interactive pieces (not yet installed
  — add it when a feature actually needs it, don't pre-install)
- Vite for asset building (see `vite.config.js`, `resources/js`, `resources/css`)
- Tailwind CSS v4, custom theme tokens (colors/fonts) in `resources/css/app.css`
  — deep blue + cyan palette sampled directly from `public/images/logo/winson-logo.png`'s
  gradient (see the file's header comment), not a copy of winsonchina.com's
  actual (generic Shopify-style) visual design. Re-derive from the logo again
  if the logo ever changes.
- SQLite/whatever `.env` currently points at for local dev

## Localization — uz/ru only, no English

The site ships Uzbek and Russian only; there is no English locale.
- `config('app.supported_locales')` = `['uz', 'ru']`; default locale `uz`,
  fallback `ru` (`config/app.php`, `.env`).
- Public URLs are locale-prefixed: `/uz/…`, `/ru/…` (route group `{locale}` in
  `routes/web.php`). `App\Http\Middleware\SetLocale` takes the locale from the
  URL, **removes the `locale` route parameter** (so controller methods never
  receive it — don't add a `$locale` argument) and sets
  `URL::defaults(['locale' => …])`, so `route('catalog.index')` works without
  passing it. `/` 302s to the visitor's language; old unprefixed
  `/katalog/…`, `/news/…` and pre-rename category slugs 301 (LocaleController).
- Use `config('app.default_locale')` (fixed `uz`) for "the site's default
  language" — `config('app.locale')` is overwritten by `App::setLocale()`.
- `/admin` is NOT prefixed; its UI language is the session (`/til/{lang}`).
- Admin-entered site links (menu, banners, hero buttons) are stored without a
  prefix (`/katalog`, `/#about`); `SafeUrl::href()` adds the current locale.
- All copy lives in `lang/uz/site.php` and `lang/ru/site.php` (dot-notation
  keys, e.g. `site.hero.title`). Never hardcode English (or any) UI strings
  directly in Blade — add a key to both files and use `__('site....')`.

## Images

Static images live under `public/images/`, split by purpose:
- `public/images/logo/` — brand/logo assets (`winson-logo.png` is the real
  logo pulled from winsonchina.com's CDN, background keyed to transparent via
  PHP GD since the source PNG had opaque-white baked in — reuse it, don't redraw it)
- `public/images/products/` — category/product illustrations. 8 hand-drawn
  120×120 SVG device illustrations (one per category slug, e.g.
  `handheld.svg`): dark gradient housings, cyan scan windows/beams, light
  `#e8eff8` tile background. They double as the product-image fallback, so
  they must read well both at 64px and large (item page). Placeholders until
  real product photos exist
- `public/images/news/` — News article cover SVGs (one per article slug)
- `public/images/misc/` — everything else (icons, backgrounds, etc.)

Reference them with `asset('images/...')`.

## Catalog (Categories & Products)

Real Eloquent-backed catalog, not static Blade:
- Models `App\Models\Category` / `App\Models\Product` (migrations:
  `create_categories_table`, `create_products_table`). Both have `name_uz`/
  `name_ru` (and Product has `description_uz`/`description_ru`) columns plus
  a `name`/`description` accessor that resolves to the current locale —
  no translation package needed since it's only two locales.
- Routes (`routes/web.php`): `catalog.index` (`/katalog`), `catalog.category`
  (`/katalog/{category:slug}`), `catalog.item` (`/katalog/{category:slug}/{product:slug}`)
  — `CatalogController`, views under `resources/views/catalog/`.
- `database/seeders/CatalogSeeder.php` seeds 8 categories × 2 products each.
  The homepage products grid and `/katalog` both render DB categories via the
  shared `partials/category-card.blade.php` (lang `categories` keys are now
  only used by the footer links). Product names are real Winson model names pulled from
  winsonchina.com listings; descriptions are original placeholder copy.
- No price field anywhere — stay consistent with the "contact for pricing"
  model from `.claude/winsonchina-reference.md`.

## Catalog: parameters, gallery, similar items

Beyond the base `name`/`description` per product:
- **Structured characteristics** (the main spec system): `features`
  (definitions: code, group, type select|multi|boolean|number, uz/ru name +
  unit, is_filterable), `feature_options`, `feature_values` (product_id,
  feature_id, feature_option_id | value_number; boolean = 1/0). Models
  `Feature`/`FeatureOption`/`FeatureValue`, `Product::featureValues()`.
  `FeatureSeeder` holds the full barcode-scanner parameter catalog (39
  params); `ProductFeatureSeeder` fills ONLY facts stated in the demo
  descriptions — real values must come from Winson datasheets, never guessed.
  Admin: /admin/features (CRUD; type is locked once created) and a grouped
  card on the product form. `App\Support\FeatureTable::for($product)` gives
  grouped display rows (item spec table, JSON-LD additionalProperty).
- Catalog filters: `App\Support\ProductFilter` (in-memory faceting on the
  category page; `?f[code][]=option`, `?f[code]=1`, `?f[code][min|max]=`).
  OR within a feature, AND across; facet counts exclude the feature's own
  selection. Filtered URLs are `noindex`. The old `products.sensor_type`
  column is gone — sensor is the `sensor` feature.
- `product_attributes` (label_uz/ru, value_uz/ru) — free-form EXTRA spec rows
  ("Qo'shimcha parametrlar") for anything that isn't a Feature.
- `product_images` (product_id, path, sort_order) — gallery beyond the single
  `products.image` cover. `Product::images()` orders by `sort_order`.
- `product_related` (product_id, related_product_id) — admin-curated "similar
  items" for a product. `Product::relatedProducts()`; when a product has none
  picked, fall back to other products in the same category (see
  `CatalogController@item`) so the section is never empty.
- Category/Product/Article all expose an `image_url` accessor that resolves
  either a static `public/images/...` path (the seeded SVG placeholders) or
  an admin-uploaded file under the `public` disk (`storage/app/public/...`,
  served via the `/storage` symlink — run `php artisan storage:link` after a
  fresh clone). Always use `->image_url` in views, never build the URL by hand.

## News

DB-backed (`articles` table), not static PHP — admin-managed via `/admin`:
- `App\Models\Article`: `title_uz/ru`, `excerpt_uz/ru`, `body_uz/ru` (HTML from
  the admin rich-text editor), `image` (cover, removable), `published_at`,
  `read_minutes`, plus the same locale accessors (`title`, `excerpt`, `body`)
  and `image_url` pattern as Category/Product.
- Rich text: Quill 2 (`resources/js/admin-editor.js`, its own Vite entry,
  loaded only on the article form). Every body is run through
  `App\Support\RichText::clean()` on save (tag/attribute whitelist, SafeUrl
  links, images only from `/storage/articles/inline` or `/images`), so the
  public view prints it with `{!! !!}` — never store unsanitized HTML there.
  Editor images upload to `admin.article-images.store`; images removed from
  the text are deleted on save, and `articles:prune-images` (scheduled daily —
  needs the scheduler cron) removes uploads no article uses.
- Routes: `news.index` (`/news`), `news.show` (`/news/{slug}`) —
  `NewsController`, views under `resources/views/news/`.
- `app/Support/Articles.php` (the original 7 hand-written uz/ru articles) is
  kept only as the seed source for `ArticleSeeder` — don't read it at
  request-time; the DB is the source of truth once seeded.

## Admin CMS (`/admin`)

- Auth: existing `users` table, Laravel's built-in `Auth`/`auth` guard, no
  roles/permissions — any row in `users` is an admin. Login at
  `admin.login` (outside auth middleware), everything else under `/admin`
  behind the `admin.auth` middleware (redirects guests to `admin.login`
  instead of the default `login` route name, since there's no public login).
  The admin user is created by `AdminUserSeeder` from `ADMIN_NAME`/`ADMIN_EMAIL`/
  `ADMIN_PASSWORD` in `.env` (via `config/admin.php`; skipped if unset) —
  never hardcode credentials in the seeder.
- `HomeContent` (single row, id=1, `HomeContent::current()`) holds the
  homepage's editable hero/about/cta text, uz+ru columns per field (e.g.
  `hero_title_uz`/`hero_title_ru`). Read it in Blade via `->t('hero_title')`
  (resolves current locale). Edited from one admin form
  (`admin.page-content.edit`/`update`), not a list-style resource.
- `Setting` is a generic key/value table (`Setting::get('key')`,
  `Setting::set('key', $value)`) for site-wide, non-translatable technical
  config: `google_analytics_id`, `yandex_metrica_id`,
  `google_site_verification`, `yandex_site_verification`. The layout head
  should read these and emit the verification `<meta>` tags / analytics
  snippets only when set (never hardcode a real tracking ID in source).
- Resource CRUD (`Route::resource`, `except(['show'])`) for categories,
  products, articles under `admin.*` route names. Product's edit form is the
  complex one: name/description fields, characteristics (features), cover image, gallery
  upload (multiple files), a small vanilla-JS "add row" repeater for
  attributes, and a related-items multi-select — see "Catalog: parameters,
  gallery, similar items" above for the underlying schema.
- No Livewire/Filament dependency added for this — plain Blade forms, normal
  multipart POSTs, minimal vanilla JS for repeater rows. Keep it that way
  unless a specific piece of admin UX genuinely can't work without real-time
  reactivity; don't reach for Livewire by default just because it's
  mentioned elsewhere in this file as the plan for the *public* site.

## Banners, menu, contacts

- `banners` (`App\Models\Banner`, admin `admin.banners.*`): homepage slider
  above the hero (`partials/banner-slider.blade.php`, vanilla JS, pauses on
  hover/focus, honours reduced motion). Image required, all text optional;
  hidden entirely when no banner is active. Read text via `->t('title')`.
- `menu_items` (`App\Models\MenuItem`, admin `admin.menu.edit`/`update`): the
  public header nav, edited as one list and saved wholesale (row order =
  `sort_order`). Seeded from the old hard-coded links by `MenuSeeder`. Header
  uses `MenuItem::header()`. The "Narx so'rash" button is not a menu item.
- Contact details (email/phone/WhatsApp/Telegram/address uz+ru) are `Setting`
  keys edited on the Settings page; read them only through
  `App\Support\Contacts` (`all()`, `primaryUrl($subject)`), which builds the
  mailto/tel/wa.me/t.me links.

## Security notes

- Admin-entered link targets (banner link, menu URL) must pass
  `App\Support\SafeUrl` — validate with `'regex:'.SafeUrl::PATTERN` and render
  via `SafeUrl::href()`. Blocks `javascript:`/`data:` and `//host` links.
- Uploads: jpg/jpeg/png/webp only — **no SVG** (an uploaded SVG served from
  `/storage` can run script on our origin).
- Never `return back()` from a public route without checking the host —
  it trusts the Referer header (see `/til/{locale}`).
- `SecurityHeaders` middleware adds nosniff / SAMEORIGIN / Referrer-Policy
  (+ noindex, no-store on `/admin`). Files under `/storage` bypass Laravel:
  set the same headers in the web server config.
- Production: `APP_DEBUG=false`, `APP_ENV=production`, HTTPS with
  `SESSION_SECURE_COOKIE=true`, real `APP_URL`, strong `ADMIN_PASSWORD`.
- Login is throttled (10/min); lang/{uz,ru}/validation.php holds the
  validation messages + field names — add new form fields to `attributes`.

## Site search

- `App\Support\SiteSearch`: in-memory search over categories, products,
  articles (fine for a catalog of tens/hundreds of items — switch to a real
  index only if it grows a lot). Both languages searched on every page;
  model numbers match without dashes; Uzbek apostrophe variants and ё/е
  normalized; product characteristics (feature option labels) and extra
  specs are searchable; every query word must match (AND), ranked by field.
- UI: header button / `/` / Ctrl⌘+K opens `partials/search-dialog` (native
  `<dialog>`, ARIA combobox+listbox) driven by `resources/js/search.js`
  (debounced fetch of `search.suggest` JSON, results built with DOM APIs —
  never innerHTML from data; recent searches in localStorage). Full page:
  `search.index` (`/{locale}/search?q=`), `noindex`, helpful no-results state.
  `SiteSearch::highlight()` is the only safe way to mark matches in Blade.
- Searches from the results page are logged to `search_queries` (not bots,
  not logged-in admins); /admin/statistics shows top and zero-result queries.
- WebSite JSON-LD carries a `SearchAction` pointing at `search.index`.

## Visit statistics

- Self-hosted, no external service: `App\Http\Middleware\TrackPageView` (web
  group, runs in `terminate()` so it never slows responses) writes one
  `page_views` row per public GET 200 page view — path, route name, the
  viewed Product/Category/Article (morph `subject`, short names via the morph
  map in `AppServiceProvider`), locale, external referrer host, and a salted
  daily `visitor_hash` (no raw IPs stored). Skips `/admin`, logged-in admins,
  bots, prefetches, non-200s.
- Admin page `admin.statistics` (`/admin/statistics?days=1|7|30|90|0`):
  totals, daily bar chart, top URLs, top products/articles/categories,
  referrers. Product/article admin lists show a 30-day views column.
- Stores by raw path, so once locale-prefixed URLs land, `/uz/x` and `/ru/x`
  are separate rows (per-item stats are unaffected — they key on `subject`).

## SEO

Implemented (audit by the searchfit-seo auditor, 2026-09-30):
- `App\Support\Seo` (request-scoped): page views set it at the top of the
  template — `Seo::page()->title()->description()->image()->crumb()->node()` —
  and `partials/seo-head.blade.php` renders `<title>`, description, canonical,
  hreflang (uz/ru/x-default), Open Graph/Twitter, favicons and one JSON-LD
  `@graph` (Organization + WebSite sitewide, BreadcrumbList, plus page nodes
  from `App\Support\SchemaOrg`: Product without offers — never invent prices —
  BlogPosting, CollectionPage/ItemList). `partials/breadcrumbs` shows the same
  crumbs visibly. New public pages must set Seo, not `@section('title')`.
- SVG images are skipped for og:image; fallback `public/images/og-default.png`.
  Favicons: `public/favicon.svg|ico|-32.png`, `apple-touch-icon.png`.
- Admin SEO tools: `admin.partials.seo-fields` (UZ/RU title + description,
  counters, Google preview) on categories, products, articles
  (`meta_title_*`, `meta_description_*`, trait `HasSeoFields`) and the hero page
  (homepage `home_contents.seo_*`). Categories have an intro text
  (`description_uz/ru`). Settings → SEO: indexing switch (off = robots.txt
  `Disallow: /`), catalog/news listing titles+descriptions
  (`Setting::localized()`), social profile URLs (`seo_same_as` → sameAs).
- `/robots.txt` and `/sitemap.xml` are routes (`SeoController`); the sitemap is
  cached and cleared on Category/Product/Article save/delete and settings save.
  Don't add static `public/robots.txt` / `sitemap.xml` — they would shadow them.
- Still open (content/owner work): real contacts, longer product/category
  copy + real photos + specs, longer articles; Figtree/Space Mono have no
  Cyrillic (Russian renders in a fallback font); production `APP_URL`;
  submit the sitemap to Google Search Console + Yandex Webmaster.

## Working notes

- Dark navy sections (home hero, home CTA box, footer) use the honeycomb
  "scan grid" backdrop `partials/hex-grid.blade.php` — pass a unique `id`
  per include (SVG ids must not clash on one page) and optional `lit`/`pulse`
  cells. No scanline/sweeping-laser animations anywhere (removed on request).

- Homepage (`resources/views/home.blade.php`) is a themed teaser that links
  out to the real `/katalog` and `/news` pages — keep those links working
  when editing either.
- Prefer server-rendered Blade + Livewire over adding a JS framework/SPA layer.
- Since this is a small, low-traffic informational site, favor straightforward
  Eloquent/Blade solutions over premature abstraction (no need for
  API layers, queues, or multi-tenancy).
- See `.claude/winsonchina-reference.md` for the structure/content model this
  site is based on (winsonchina.com) — content/structure reference only, not
  a visual design reference.

## Agents & skills

Custom subagents live in `.claude/agents/`: `laravel-builder` (feature work),
`content-importer` (turning raw product data into seeders), `ui-checker`
(visual verification in Chrome). Prefer them for their respective jobs over
doing the work inline.

Favor these skills on this project: `laravel-expert` for architecture/best
practices, `frontend-design` before writing any Blade/Tailwind markup so
pages don't look generic, and `run` / `claude-in-chrome` to actually launch
the app and check pages visually rather than trusting that code compiles.
