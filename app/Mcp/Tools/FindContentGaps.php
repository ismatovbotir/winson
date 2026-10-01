<?php

namespace App\Mcp\Tools;

use App\Models\Article;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Product;
use App\Support\RichText;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('find_content_gaps')]
#[Description('SEO/content audit of the catalog: products with short descriptions, few characteristics, placeholder images or no SEO fields; categories without intro text; short articles; and visitor searches that returned nothing (last 90 days). Use it to plan what to write or fill next.')]
class FindContentGaps extends Tool
{
    public function handle(Request $request): Response
    {
        $filterable = Feature::where('is_filterable', true)->count();
        $words = fn ($html) => preg_match_all('/[\p{L}\p{N}]+/u', RichText::text($html));

        $products = Product::with('category')->withCount(['featureValues as features_filled' => fn ($q) => $q->select(DB::raw('COUNT(DISTINCT feature_id)'))])->get()
            ->map(fn ($p) => array_filter([
                'slug' => $p->slug,
                'issues' => array_values(array_filter([
                    mb_strlen((string) $p->description_ru) < 300 ? 'short description (ru '.mb_strlen((string) $p->description_ru).' chars; aim 600+)' : null,
                    mb_strlen((string) $p->description_uz) < 300 ? 'short description (uz '.mb_strlen((string) $p->description_uz).' chars; aim 600+)' : null,
                    $p->features_filled < 8 ? "only {$p->features_filled} characteristics filled (of {$filterable} filterable)" : null,
                    str_starts_with((string) $p->image, 'images/') || ! $p->image ? 'placeholder image — needs a real product photo (upload in admin)' : null,
                    ! $p->meta_title_ru && ! $p->meta_title_uz ? 'no custom SEO title' : null,
                ])),
            ]))->filter(fn ($p) => $p['issues'])->values();

        return Response::json([
            'products' => $products,
            'categories_without_intro' => Category::where(fn ($q) => $q->whereNull('description_ru')->orWhere('description_ru', ''))->pluck('slug'),
            'short_articles' => Article::get()->filter(fn ($a) => $words($a->body_ru) < 600)
                ->map(fn ($a) => ['slug' => $a->slug, 'words_ru' => $words($a->body_ru), 'words_uz' => $words($a->body_uz)])->values(),
            'searches_without_results_90d' => DB::table('search_queries')->where('results', 0)->where('created_at', '>=', now()->subDays(90))
                ->select('query', DB::raw('COUNT(*) as times'))->groupBy('query')->orderByDesc('times')->limit(25)->pluck('times', 'query'),
        ]);
    }
}
