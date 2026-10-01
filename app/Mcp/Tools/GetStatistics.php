<?php

namespace App\Mcp\Tools;

use App\Models\Article;
use App\Models\Category;
use App\Models\PageView;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('get_statistics')]
#[Description('Visitor analytics for a period: views, unique visitors, daily views, top pages, top products/categories/articles, referrers, visitor search queries and searches that found nothing (content gaps). Bots and logged-in admins are excluded.')]
class GetStatistics extends Tool
{
    public function handle(Request $request): Response
    {
        $days = (int) ($request->validate(['days' => 'sometimes|integer|min:1|max:365'])['days'] ?? 30);
        $base = fn () => PageView::since($days);
        $since = now()->subDays($days - 1)->startOfDay();

        $top = function (string $class, string $route) use ($base) {
            $rows = $base()->where('subject_type', (new $class)->getMorphClass())
                ->select('subject_id', DB::raw('COUNT(*) as views'))->groupBy('subject_id')->orderByDesc('views')->limit(10)->get();
            $models = $class::findMany($rows->pluck('subject_id'))->keyBy('id');

            return $rows->filter(fn ($r) => $models->has($r->subject_id))
                ->map(fn ($r) => ['slug' => $models[$r->subject_id]->slug, 'views' => (int) $r->views])->values();
        };
        $searches = fn (bool $zero) => DB::table('search_queries')->where('created_at', '>=', $since)
            ->when($zero, fn ($q) => $q->where('results', 0))
            ->select('query', DB::raw('COUNT(*) as times'), DB::raw('MAX(results) as results'))
            ->groupBy('query')->orderByDesc('times')->limit(20)->get();

        return Response::json([
            'period_days' => $days,
            'views' => $base()->count(),
            'unique_visitors' => $base()->distinct()->count('visitor_hash'),
            'daily' => $base()->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as views'))->groupBy('day')->orderBy('day')->pluck('views', 'day'),
            'top_pages' => $base()->select('path', DB::raw('COUNT(*) as views'))->groupBy('path')->orderByDesc('views')->limit(15)->pluck('views', 'path'),
            'top_products' => $top(Product::class, 'catalog.item'),
            'top_categories' => $top(Category::class, 'catalog.category'),
            'top_articles' => $top(Article::class, 'news.show'),
            'referrers' => $base()->whereNotNull('referrer_host')->select('referrer_host', DB::raw('COUNT(*) as views'))->groupBy('referrer_host')->orderByDesc('views')->limit(10)->pluck('views', 'referrer_host'),
            'search_queries' => $searches(false),
            'search_queries_without_results' => $searches(true),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return ['days' => $schema->integer()->description('Period in days (1–365). Default 30.')];
    }
}
