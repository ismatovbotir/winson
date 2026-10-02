<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TelegramAppController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| Public site — every page lives under /uz/… or /ru/… so each language has its
| own URL (needed for hreflang/canonical). SetLocale reads the {locale}
| parameter, drops it before controllers run, and sets URL::defaults so
| route('catalog.index') etc. keep working without passing a locale.
*/
Route::get('/', [LocaleController::class, 'root'])->name('root');

Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::prefix('{locale}')->where(['locale' => 'uz|ru'])->group(function () {
    Route::get('/', HomeController::class)->name('home');

    // Old section names (/uz/katalog/…, /uz/category/…) → 301 to /uz/catalog/….
    Route::get('{old}/{path?}', [LocaleController::class, 'legacySection'])
        ->where(['old' => 'katalog|category', 'path' => '.*']);

    // Pre-rename category slugs (scan_engine → scan-engine …) → 301.
    Route::get('catalog/{slug}/{rest?}', [LocaleController::class, 'legacyCategory'])
        ->where('slug', LocaleController::legacySlugPattern());

    Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('catalog/{category:slug}', [CatalogController::class, 'category'])->name('catalog.category');
    Route::get('catalog/{category:slug}/{product:slug}', [CatalogController::class, 'item'])->name('catalog.item');

    Route::get('search', [SearchController::class, 'index'])->name('search.index');
    Route::get('search/suggest', [SearchController::class, 'suggest'])->middleware('throttle:search')->name('search.suggest');

    Route::get('news', [NewsController::class, 'index'])->name('news.index');
    Route::get('news/{article:slug}', [NewsController::class, 'show'])->name('news.show');
});

/*
| Telegram Mini App (catalog inside Telegram) + bot webhook. See
| TelegramAppController / TelegramWebhookController and `telegram:setup`.
*/
Route::get('tg', [TelegramAppController::class, 'entry'])->name('tg.entry');
Route::post('tg/lead', [TelegramAppController::class, 'lead'])->middleware('throttle:tg-lead')->name('tg.lead');
Route::prefix('tg/{locale}')->where(['locale' => 'uz|ru'])->name('tg.')->group(function () {
    Route::get('/', [TelegramAppController::class, 'home'])->name('home');
    Route::get('c/{category:slug}', [TelegramAppController::class, 'category'])->name('category');
    Route::get('p/{category:slug}/{product:slug}', [TelegramAppController::class, 'product'])->name('product');
});
// No rate limit: protected by the webhook secret, and all bot traffic comes from a few Telegram IPs.
Route::post('telegram/webhook', TelegramWebhookController::class)->name('telegram.webhook');

// URLs from before the locale prefix → 301 to the /uz/ version.
Route::get('{section}/{path?}', [LocaleController::class, 'legacy'])
    ->where(['section' => 'katalog|catalog|category|news', 'path' => '.*']);

// Admin UI language (session-based; public pages use the URL prefix instead).
Route::get('til/{lang}', function (Request $request, string $lang) {
    if (! in_array($lang, config('app.supported_locales', ['uz', 'ru']), true)) {
        abort(404);
    }

    $request->session()->put('locale', $lang);

    // Only go back to a page on this site — back() alone trusts the Referer
    // header, which would make this an open redirect.
    $previous = url()->previous('/');

    return redirect(parse_url($previous, PHP_URL_HOST) === $request->getHost() ? $previous : '/');
})->name('locale.switch');

/*
| Admin CMS — not locale-prefixed, not indexed. Any `users` row is an admin.
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');

    Route::middleware('admin.auth')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::get('statistics', Admin\StatisticsController::class)->name('statistics');

        Route::resource('categories', Admin\CategoryController::class)->except(['show']);
        Route::resource('products', Admin\ProductController::class)->except(['show']);
        Route::resource('features', Admin\FeatureController::class)->except(['show']);
        Route::resource('articles', Admin\ArticleController::class)->except(['show']);
        Route::post('article-images', Admin\ArticleImageController::class)->middleware('throttle:uploads')->name('article-images.store');
        Route::resource('banners', Admin\BannerController::class)->except(['show']);

        Route::get('menu', [Admin\MenuController::class, 'edit'])->name('menu.edit');
        Route::put('menu', [Admin\MenuController::class, 'update'])->name('menu.update');

        Route::resource('bot-knowledge', Admin\BotKnowledgeController::class)->except(['show'])->parameters(['bot-knowledge' => 'botKnowledge']);
        Route::get('bot/instructions', [Admin\BotTrainingController::class, 'instructions'])->name('bot.instructions');
        Route::put('bot/instructions', [Admin\BotTrainingController::class, 'saveInstructions'])->name('bot.instructions.save');
        Route::delete('bot/instructions', [Admin\BotTrainingController::class, 'resetInstructions'])->name('bot.instructions.reset');
        Route::get('bot/playground', [Admin\BotTrainingController::class, 'playground'])->name('bot.playground');
        Route::post('bot/playground', [Admin\BotTrainingController::class, 'ask'])->middleware('throttle:uploads')->name('bot.playground.ask');

        Route::get('leads', [Admin\LeadController::class, 'index'])->name('leads.index');
        Route::patch('leads/{lead}', [Admin\LeadController::class, 'update'])->name('leads.update');
        Route::get('telegram-clients', [Admin\TelegramClientController::class, 'index'])->name('telegram-clients.index');
        Route::get('telegram-clients/{client}', [Admin\TelegramClientController::class, 'show'])->name('telegram-clients.show');
        Route::patch('telegram-clients/{client}', [Admin\TelegramClientController::class, 'update'])->name('telegram-clients.update');

        Route::get('hero', [Admin\HeroController::class, 'edit'])->name('hero.edit');
        Route::put('hero', [Admin\HeroController::class, 'update'])->name('hero.update');

        Route::get('page-content', [Admin\PageContentController::class, 'edit'])->name('page-content.edit');
        Route::put('page-content', [Admin\PageContentController::class, 'update'])->name('page-content.update');

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');

        Route::post('mcp/token', [Admin\McpController::class, 'token'])->name('mcp.token');
        Route::delete('mcp/token', [Admin\McpController::class, 'revoke'])->name('mcp.revoke');
        Route::put('mcp/permissions', [Admin\McpController::class, 'permissions'])->name('mcp.permissions');
    });
});
