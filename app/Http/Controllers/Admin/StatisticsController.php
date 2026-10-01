<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\PageView;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /** Allowed period filters, in days (0 = all time). */
    public const PERIODS = [1, 7, 30, 90, 0];

    public function __invoke(Request $request)
    {
        $days = in_array($request->integer('days', 30), self::PERIODS, true) ? $request->integer('days', 30) : 30;
        $since = $days ?: null;

        $base = fn () => PageView::query()->since($since);

        $totals = [
            'views' => $base()->count(),
            'visitors' => $base()->distinct()->count('visitor_hash'),
            'today' => PageView::query()->since(1)->count(),
        ];

        return view('admin.statistics.index', [
            'days' => $days,
            'periods' => self::PERIODS,
            'totals' => $totals,
            'daily' => $this->daily($days),
            'pages' => $base()
                ->select('path', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT visitor_hash) as visitors'))
                ->groupBy('path')->orderByDesc('views')->limit(25)->get(),
            'products' => $this->topSubjects($base(), Product::class, ['category']),
            'articles' => $this->topSubjects($base(), Article::class),
            'categories' => $this->topSubjects($base(), Category::class),
            'searches' => $this->searches($since, false),
            'zeroSearches' => $this->searches($since, true),
            'referrers' => $base()->whereNotNull('referrer_host')
                ->select('referrer_host', DB::raw('COUNT(*) as views'))
                ->groupBy('referrer_host')->orderByDesc('views')->limit(10)->get(),
        ]);
    }

    /**
     * Views per day for the chart, zero-filled so quiet days still show.
     * "All time" and 90 days are capped to the last 90 days to keep bars readable.
     *
     * @return Collection<int, array{date: Carbon, views: int}>
     */
    private function daily(int $days): Collection
    {
        $span = $days === 0 || $days > 90 ? 90 : max($days, 7);

        $counts = PageView::query()->since($span)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as views'))
            ->groupBy('day')
            ->pluck('views', 'day');

        return collect(range($span - 1, 0))->map(function ($ago) use ($counts) {
            $date = now()->subDays($ago)->startOfDay();

            return ['date' => $date, 'views' => (int) ($counts[$date->toDateString()] ?? 0)];
        });
    }

    /** Top visitor search queries; $zero = only the ones that found nothing. */
    private function searches(?int $days, bool $zero): Collection
    {
        return DB::table('search_queries')
            ->when($days, fn ($q) => $q->where('created_at', '>=', now()->subDays($days - 1)->startOfDay()))
            ->when($zero, fn ($q) => $q->where('results', 0))
            ->select('query', DB::raw('COUNT(*) as times'), DB::raw('MAX(results) as results'), DB::raw('MAX(created_at) as last_at'))
            ->groupBy('query')->orderByDesc('times')->orderByDesc('last_at')->limit(15)->get();
    }

    /** Most-viewed models of one type, with their view/visitor counts attached. */
    private function topSubjects($query, string $class, array $with = []): Collection
    {
        $rows = $query->where('subject_type', (new $class)->getMorphClass())
            ->select('subject_id', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT visitor_hash) as visitors'))
            ->groupBy('subject_id')->orderByDesc('views')->limit(15)->get();

        $models = $class::with($with)->findMany($rows->pluck('subject_id'))->keyBy('id');

        // Rows whose model was deleted since are dropped.
        return $rows->filter(fn ($row) => $models->has($row->subject_id))
            ->map(fn ($row) => [
                'model' => $models[$row->subject_id],
                'views' => (int) $row->views,
                'visitors' => (int) $row->visitors,
            ])->values();
    }
}
