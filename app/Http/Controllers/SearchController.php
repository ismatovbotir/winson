<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TrackPageView;
use App\Models\Category;
use App\Support\SiteSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SearchController extends Controller
{
    public function __construct(private SiteSearch $search) {}

    /** Full results page: /{locale}/search?q=… */
    public function index(Request $request)
    {
        $query = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $results = $this->search->search($query);

        if ($this->search->tokens($query)) {
            $this->log($request, $query, $results['total']);
        }

        return view('search.index', [
            'query' => $query,
            'results' => $results,
            'tooShort' => $query !== '' && ! $this->search->tokens($query),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    /** Instant suggestions for the header search (JSON, a few per group). */
    public function suggest(Request $request): JsonResponse
    {
        $query = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $results = $this->search->search($query, 5);

        $strip = fn ($items) => $items->map(fn ($i) => collect($i)->only(['title', 'url', 'image', 'meta'])->all())->values();

        return response()->json([
            'query' => $query,
            'total' => $results['total'],
            'groups' => collect(['categories', 'products', 'articles'])
                ->filter(fn ($g) => $results[$g]->isNotEmpty())
                ->map(fn ($g) => ['key' => $g, 'label' => __('site.search.groups.'.$g), 'items' => $strip($results[$g])])
                ->values(),
            'all_url' => route('search.index', ['q' => $query]),
        ])->header('Cache-Control', 'private, max-age=60');
    }

    private function log(Request $request, string $query, int $results): void
    {
        if (Auth::check() || preg_match(TrackPageView::BOT_PATTERN, (string) $request->userAgent())) {
            return;
        }

        try {
            DB::table('search_queries')->insert([
                'query' => mb_substr($this->search->normalize($query), 0, 100),
                'locale' => app()->getLocale(),
                'results' => $results,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e); // logging must never break search
        }
    }
}
