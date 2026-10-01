<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
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

    // Pre-rename category slugs (scan_engine → scan-engine …) → 301.
    Route::get('katalog/{slug}/{rest?}', [LocaleController::class, 'legacyCategory'])
        ->where('slug', LocaleController::legacySlugPattern());

    Route::get('katalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('katalog/{category:slug}', [CatalogController::class, 'category'])->name('catalog.category');
    Route::get('katalog/{category:slug}/{product:slug}', [CatalogController::class, 'item'])->name('catalog.item');

    Route::get('search', [SearchController::class, 'index'])->name('search.index');
    Route::get('search/suggest', [SearchController::class, 'suggest'])->middleware('throttle:60,1')->name('search.suggest');

    Route::get('news', [NewsController::class, 'index'])->name('news.index');
    Route::get('news/{article:slug}', [NewsController::class, 'show'])->name('news.show');
});

// URLs from before the locale prefix → 301 to the /uz/ version.
Route::get('{section}/{path?}', [LocaleController::class, 'legacy'])
    ->where(['section' => 'katalog|news', 'path' => '.*']);

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
    Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');

    Route::middleware('admin.auth')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::get('statistics', Admin\StatisticsController::class)->name('statistics');

        Route::resource('categories', Admin\CategoryController::class)->except(['show']);
        Route::resource('products', Admin\ProductController::class)->except(['show']);
        Route::resource('features', Admin\FeatureController::class)->except(['show']);
        Route::resource('articles', Admin\ArticleController::class)->except(['show']);
        Route::post('article-images', Admin\ArticleImageController::class)->middleware('throttle:60,1')->name('article-images.store');
        Route::resource('banners', Admin\BannerController::class)->except(['show']);

        Route::get('menu', [Admin\MenuController::class, 'edit'])->name('menu.edit');
        Route::put('menu', [Admin\MenuController::class, 'update'])->name('menu.update');

        Route::get('hero', [Admin\HeroController::class, 'edit'])->name('hero.edit');
        Route::put('hero', [Admin\HeroController::class, 'update'])->name('hero.update');

        Route::get('page-content', [Admin\PageContentController::class, 'edit'])->name('page-content.edit');
        Route::put('page-content', [Admin\PageContentController::class, 'update'])->name('page-content.update');

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });
});
