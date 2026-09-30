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
- `App\Http\Middleware\SetLocale` (registered globally in `bootstrap/app.php`)
  resolves locale from session → browser `Accept-Language` → config default.
- Switch via `GET /til/{locale}` (route name `locale.switch`), which just
  stores the choice in session and redirects back — see the switcher in
  `resources/views/partials/header.blade.php`. **Planned to be replaced** by
  locale-prefixed URLs (`/uz/...`/`/ru/...`) — see the SEO section below —
  check `routes/web.php` for which scheme is actually live before assuming.
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
- `product_attributes` (product_id, label_uz/ru, value_uz/ru, sort_order) —
  free-form spec rows (e.g. "Interfeys: USB"), admin-managed, rendered as a
  spec table on the item page. Not the same as `sensor_type`, which stays a
  simple structured column (ccd/cmos/laser) used for filtering.
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
  complex one: name/description fields, sensor_type, cover image, gallery
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

Agreed direction (implement/verify against actual code — this may be ahead
of or behind what's committed at any given moment):
- **Locale-prefixed URLs** (`/uz/...`, `/ru/...`) replacing the old
  session-based `/til/{locale}` switcher, specifically so `hreflang`
  alternate-language tags are possible (they need a distinct URL per
  language to point at). Bare `/` redirects to the browser's preferred
  supported locale. `admin/*` routes are NOT locale-prefixed (single admin
  UI, not indexed).
- Per-page `<title>` and meta description on every page (including
  home/catalog-index/category/news-index, which were initially missed).
- Open Graph + Twitter Card tags, `<link rel="canonical">`, `hreflang`
  alternates (including `x-default`) in the layout `<head>`.
- JSON-LD: sitewide `Organization`, `Product` on item pages, `Article` on
  news show pages.
- `/sitemap.xml` and `/robots.txt` served via real routes/controllers (not
  static files in `public/`) so they can include the real domain and both
  locale URLs per page — delete the static `public/robots.txt` /
  `public/favicon.ico` stub if they'd otherwise shadow the dynamic route.
- Real favicon generated from the Winson logo (brand navy + a simple mark),
  referenced via explicit `<link rel="icon">`/`apple-touch-icon"` tags rather
  than relying on the implicit `/favicon.ico` lookup.

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
