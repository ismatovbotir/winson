<?php

namespace App\Mcp\Support;

use App\Models\Article;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Product;
use App\Support\FeatureTable;
use App\Support\RichText;
use Illuminate\Support\Str;

/** Models → plain arrays for MCP tool responses (both languages + URLs). */
class Presenter
{
    public static function urls(string $route, array $params): array
    {
        return collect(config('app.supported_locales', ['uz', 'ru']))
            ->mapWithKeys(fn ($l) => [$l => route($route, array_merge($params, ['locale' => $l]))])
            ->all();
    }

    public static function category(Category $c, bool $full = false): array
    {
        $data = [
            'slug' => $c->slug,
            'name' => ['uz' => $c->name_uz, 'ru' => $c->name_ru],
            'products_count' => $c->products_count ?? $c->products()->count(),
            'sort_order' => $c->sort_order,
            'urls' => self::urls('catalog.category', [$c]),
            'admin_url' => route('admin.categories.edit', $c),
        ];

        if ($full) {
            $data += [
                'description' => ['uz' => $c->description_uz, 'ru' => $c->description_ru],
                'seo' => self::seo($c),
                'image' => $c->image_url,
            ];
        }

        return $data;
    }

    public static function product(Product $p, bool $full = false): array
    {
        $data = [
            'slug' => $p->slug,
            'name' => ['uz' => $p->name_uz, 'ru' => $p->name_ru],
            'category' => $p->category?->slug,
            'urls' => $p->category ? self::urls('catalog.item', [$p->category, $p]) : null,
            'admin_url' => route('admin.products.edit', $p),
        ];

        if (! $full) {
            $data['description_length'] = ['uz' => mb_strlen((string) $p->description_uz), 'ru' => mb_strlen((string) $p->description_ru)];
            $data['features_filled'] = $p->featureValues()->distinct('feature_id')->count('feature_id');

            return $data;
        }

        $p->loadMissing('featureValues.feature', 'featureValues.option', 'specs', 'images', 'relatedProducts');

        $features = $p->featureValues->groupBy('feature_id')->map(function ($rows) {
            $f = $rows->first()->feature;

            return [$f->code => match ($f->type) {
                'select' => $rows->first()->option?->code,
                'multi' => $rows->map(fn ($r) => $r->option?->code)->filter()->values()->all(),
                'boolean' => (bool) (float) $rows->first()->value_number,
                'number' => (float) $rows->first()->value_number,
            }];
        })->collapse()->all();

        app()->setLocale('ru');
        $readable = FeatureTable::for($p)->flatten(1)->mapWithKeys(fn ($r) => [$r[0]->name => $r[1]])->all();
        app()->setLocale(config('app.default_locale'));

        return $data + [
            'description' => ['uz' => $p->description_uz, 'ru' => $p->description_ru],
            'features' => $features,
            'features_readable_ru' => $readable,
            'extra_specs' => $p->specs->map(fn ($s) => ['label' => ['uz' => $s->label_uz, 'ru' => $s->label_ru], 'value' => ['uz' => $s->value_uz, 'ru' => $s->value_ru]])->all(),
            'cover_image' => $p->image_url,
            'gallery_count' => $p->images->count(),
            'related' => $p->relatedProducts->pluck('slug')->all(),
            'seo' => self::seo($p),
            'sort_order' => $p->sort_order,
        ];
    }

    public static function article(Article $a, bool $full = false): array
    {
        $data = [
            'slug' => $a->slug,
            'title' => ['uz' => $a->title_uz, 'ru' => $a->title_ru],
            'published_at' => $a->published_at?->toDateString(),
            'urls' => self::urls('news.show', [$a]),
            'admin_url' => route('admin.articles.edit', $a),
        ];

        // Unicode-aware (Uzbek oʻ/gʻ apostrophes and hyphenated words count as one word).
        $words = fn ($html) => preg_match_all("/[\\p{L}\\p{N}][\\p{L}\\p{N}'\\x{02BB}\\x{2019}-]*/u", RichText::text($html));

        if (! $full) {
            return $data + ['words' => ['uz' => $words($a->body_uz), 'ru' => $words($a->body_ru)]];
        }

        return $data + [
            'excerpt' => ['uz' => $a->excerpt_uz, 'ru' => $a->excerpt_ru],
            'body_html' => ['uz' => $a->body_uz, 'ru' => $a->body_ru],
            'words' => ['uz' => $words($a->body_uz), 'ru' => $words($a->body_ru)],
            'read_minutes' => $a->read_minutes,
            'cover_image' => $a->image_url,
            'seo' => self::seo($a),
        ];
    }

    public static function feature(Feature $f): array
    {
        return array_filter([
            'code' => $f->code,
            'group' => $f->group,
            'type' => $f->type,
            'name' => ['uz' => $f->name_uz, 'ru' => $f->name_ru],
            'unit' => $f->unit_uz || $f->unit_ru ? ['uz' => $f->unit_uz, 'ru' => $f->unit_ru] : null,
            'filterable' => $f->is_filterable,
            'options' => $f->hasOptions() ? $f->options->map(fn ($o) => ['code' => $o->code, 'uz' => $o->label_uz, 'ru' => $o->label_ru])->all() : null,
        ], fn ($v) => $v !== null);
    }

    private static function seo($model): array
    {
        return [
            'meta_title' => ['uz' => $model->meta_title_uz, 'ru' => $model->meta_title_ru],
            'meta_description' => ['uz' => $model->meta_description_uz, 'ru' => $model->meta_description_ru],
        ];
    }

    public static function excerpt(?string $text, int $limit = 160): ?string
    {
        return $text ? Str::limit(RichText::text($text), $limit) : null;
    }
}
