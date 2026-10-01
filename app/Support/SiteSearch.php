<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Visitor search over categories, products and articles.
 *
 * In-memory and forgiving on purpose (the catalog is tens of items, not
 * thousands): both languages are searched whatever the page locale, model
 * numbers match without dashes/spaces ("wnl7000" → WNL-7000G), Uzbek
 * apostrophe variants (o' oʻ o‘ o’) and ё/е are treated as equal, and product
 * characteristics count ("bluetooth", "QR", "IP65"). Every word of the query
 * must match somewhere (AND); results are ranked by where it matched.
 */
class SiteSearch
{
    public const MIN_LENGTH = 2;

    /** @return array{categories: Collection, products: Collection, articles: Collection, total: int} */
    public function search(string $query, ?int $limit = null): array
    {
        $tokens = $this->tokens($query);
        $empty = ['categories' => collect(), 'products' => collect(), 'articles' => collect(), 'total' => 0];
        if (! $tokens) {
            return $empty;
        }

        $result = [
            'categories' => $this->rank($this->categoryDocs(), $tokens, $limit),
            'products' => $this->rank($this->productDocs(), $tokens, $limit),
            'articles' => $this->rank($this->articleDocs(), $tokens, $limit),
        ];
        $result['total'] = $result['categories']->count() + $result['products']->count() + $result['articles']->count();

        return $result;
    }

    /** Words of the query worth searching for (normalized). */
    public function tokens(string $query): array
    {
        $query = $this->normalize(Str::limit($query, 100, ''));

        return collect(preg_split('/[\s,;]+/u', $query))
            ->map(fn ($t) => trim($t, ".:!?()[]\"'"))
            ->filter(fn ($t) => mb_strlen($t) >= self::MIN_LENGTH)
            ->unique()->take(8)->values()->all();
    }

    /** Escape $text and wrap the query words in <mark> (safe: both sides escaped). */
    public function highlight(?string $text, string $query): \Illuminate\Support\HtmlString
    {
        $html = e((string) $text);
        $tokens = array_map(fn ($t) => preg_quote(e($t), '/'), $this->tokens($query));
        if ($tokens) {
            // Skip HTML entities (&amp; …) so a word can never be marked inside one.
            $html = preg_replace_callback(
                '/(&#?[a-z0-9]+;)|('.implode('|', $tokens).')/iu',
                fn ($m) => $m[1] !== '' ? $m[1] : '<mark class="rounded-sm bg-accent-soft px-0.5 text-navy">'.$m[2].'</mark>',
                $html
            );
        }

        return new \Illuminate\Support\HtmlString($html);
    }

    /** Lowercase, unify apostrophes and ё, collapse spaces. */
    public function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = str_replace(['ʻ', 'ʼ', '‘', '’', '`', '´', 'ё'], ["'", "'", "'", "'", "'", "'", 'е'], $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** Letters and digits only — for model numbers typed without "-" or spaces. */
    private function compact(string $text): string
    {
        return preg_replace('/[^\p{L}\p{N}]+/u', '', $text);
    }

    // ---- ranking ----

    /**
     * @param  Collection<int, array{item: array, fields: array<int, array{0: string, 1: int}>}>  $docs  fields: [normalized text, weight]
     */
    private function rank(Collection $docs, array $tokens, ?int $limit): Collection
    {
        $ranked = $docs->map(function ($doc) use ($tokens) {
            $score = 0;
            foreach ($tokens as $token) {
                $best = 0;
                $compactToken = $this->compact($token);
                foreach ($doc['fields'] as [$text, $weight]) {
                    if ($text === '') {
                        continue;
                    }
                    $s = 0;
                    if ($text === $token) {
                        $s = $weight * 4;                                   // whole field
                    } elseif (preg_match('/(^|[^\p{L}\p{N}])'.preg_quote($token, '/').'/u', $text)) {
                        $s = $weight * 2;                                   // start of a word
                    } elseif (str_contains($text, $token)) {
                        $s = $weight;                                       // inside a word
                    } elseif (mb_strlen($compactToken) >= 3 && str_contains($this->compact($text), $compactToken)) {
                        $s = $weight;                                       // "wnl7000" ~ "wnl-7000g"
                    }
                    $best = max($best, $s);
                }
                if ($best === 0) {
                    return null; // every word must match somewhere
                }
                $score += $best;
            }

            return $doc['item'] + ['score' => $score];
        })->filter()->sortByDesc('score')->values();

        return $limit ? $ranked->take($limit) : $ranked;
    }

    // ---- documents (both locales searchable; display in the current one) ----

    private function categoryDocs(): Collection
    {
        return once(fn () => Category::withCount('products')->orderBy('sort_order')->get()->map(fn (Category $c) => [
            'item' => [
                'type' => 'category',
                'title' => $c->name,
                'url' => route('catalog.category', $c),
                'image' => $c->image_url,
                'meta' => trans_choice('site.search.models', $c->products_count, ['count' => $c->products_count]),
                'snippet' => $c->description ? Str::limit($c->description, 140) : null,
            ],
            'fields' => [
                [$this->normalize($c->name_uz), 10], [$this->normalize($c->name_ru), 10],
                [$this->normalize($c->slug), 6],
                [$this->normalize((string) $c->description_uz), 2], [$this->normalize((string) $c->description_ru), 2],
            ],
        ]));
    }

    private function productDocs(): Collection
    {
        return once(fn () => Product::with(['category', 'featureValues.option', 'specs'])->orderBy('sort_order')->get()->map(function (Product $p) {
            $features = $p->featureValues->map(fn ($v) => $v->option ? $v->option->label_uz.' '.$v->option->label_ru : null)->filter()->implode(' ');
            $specs = $p->specs->map(fn ($s) => "{$s->label_uz} {$s->value_uz} {$s->label_ru} {$s->value_ru}")->implode(' ');

            return [
                'item' => [
                    'type' => 'product',
                    'title' => $p->name,
                    'url' => $p->category ? route('catalog.item', [$p->category, $p]) : route('catalog.index'),
                    'image' => $p->image_url,
                    'meta' => $p->category?->name,
                    'snippet' => $p->description ? Str::limit($p->description, 140) : null,
                ],
                'fields' => [
                    [$this->normalize($p->name_uz), 10], [$this->normalize($p->name_ru), 10],
                    [$this->normalize($p->slug), 9],
                    [$this->normalize((string) $p->category?->name_uz.' '.$p->category?->name_ru), 3],
                    [$this->normalize($features), 4],
                    [$this->normalize((string) $p->description_uz), 2], [$this->normalize((string) $p->description_ru), 2],
                    [$this->normalize($specs), 2],
                ],
            ];
        }));
    }

    private function articleDocs(): Collection
    {
        return once(fn () => Article::orderByDesc('published_at')->get()->map(fn (Article $a) => [
            'item' => [
                'type' => 'article',
                'title' => $a->title,
                'url' => route('news.show', $a),
                'image' => $a->image_url,
                'meta' => $a->published_at?->format('d.m.Y'),
                'snippet' => Str::limit((string) $a->excerpt, 140),
            ],
            'fields' => [
                [$this->normalize($a->title_uz), 8], [$this->normalize($a->title_ru), 8],
                [$this->normalize($a->excerpt_uz.' '.$a->excerpt_ru), 3],
                [$this->normalize(RichText::text($a->body_uz).' '.RichText::text($a->body_ru)), 1],
            ],
        ]));
    }
}
