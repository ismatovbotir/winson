<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        // Short names stored in page_views.subject_type.
        Relation::morphMap([
            'product' => Product::class,
            'category' => Category::class,
            'article' => Article::class,
        ]);
    }
}
