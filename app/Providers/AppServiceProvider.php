<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One SEO state per request (filled by page views, rendered by the layout).
        $this->app->scoped(\App\Support\Seo::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Named limiters: a plain throttle:N,1 keys by IP only and is SHARED across
        // routes, so e.g. search typing could block a visitor's lead form.
        $byIp = fn (string $name, int $perMinute) => RateLimiter::for($name,
            fn (Request $request) => Limit::perMinute($perMinute)->by($name.'|'.$request->ip()));
        $byIp('search', 60);
        $byIp('tg-lead', 10);
        $byIp('admin-login', 10);
        $byIp('uploads', 60);
        RateLimiter::for('mcp', fn (Request $request) => Limit::perMinute(60)->by('mcp|'.sha1((string) $request->bearerToken())));

        // Short names stored in page_views.subject_type.
        Relation::morphMap([
            'product' => Product::class,
            'category' => Category::class,
            'article' => Article::class,
        ]);
    }
}
