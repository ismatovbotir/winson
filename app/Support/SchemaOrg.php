<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/** JSON-LD node builders for App\Support\Seo::node(). */
class SchemaOrg
{
    /** Product without offers: prices are "on request" on this site, never invented. */
    public static function product(Product $product, Category $category): array
    {
        $seo = Seo::page();
        $images = collect([$product->image_url])
            ->merge($product->images->pluck('url'))
            ->filter()->map(fn ($u) => $seo->absolute($u))->unique()->values()->all();

        $properties = collect();
        foreach (FeatureTable::for($product)->flatten(1) as [$feature, $value]) {
            $properties->push(['name' => $feature->name, 'value' => $value]);
        }
        foreach ($product->specs as $spec) {
            $properties->push(['name' => $spec->label, 'value' => $spec->value]);
        }

        return array_filter([
            '@type' => 'Product',
            '@id' => $seo->canonical().'#product',
            'name' => $product->name,
            'description' => $product->description ? RichText::text($product->description) : null,
            'image' => $images ?: null,
            'url' => $seo->canonical(),
            'brand' => ['@type' => 'Brand', 'name' => Seo::SITE_NAME],
            'manufacturer' => Seo::organizationRef(),
            'category' => $category->name,
            'additionalProperty' => $properties->map(fn ($p) => ['@type' => 'PropertyValue'] + $p)->all() ?: null,
        ]);
    }

    public static function article(Article $article): array
    {
        $seo = Seo::page();

        return array_filter([
            '@type' => 'BlogPosting',
            '@id' => $seo->canonical().'#article',
            'headline' => $article->title,
            'description' => $article->excerpt,
            'image' => $article->image_url ? $seo->absolute($article->image_url) : null,
            'datePublished' => $article->published_at?->toDateString(),
            'dateModified' => $article->updated_at?->toAtomString(),
            'inLanguage' => app()->getLocale(),
            'mainEntityOfPage' => $seo->canonical(),
            'author' => Seo::organizationRef(),
            'publisher' => Seo::organizationRef(),
        ]);
    }

    /**
     * A listing page (catalog, category, news) as CollectionPage + ItemList.
     *
     * @param  Collection<int, array{name: string, url: string}>  $items
     */
    public static function collection(string $name, Collection $items): array
    {
        $seo = Seo::page();

        return [
            '@type' => 'CollectionPage',
            '@id' => $seo->canonical().'#page',
            'name' => $name,
            'url' => $seo->canonical(),
            'inLanguage' => app()->getLocale(),
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $items->count(),
                'itemListElement' => $items->values()->map(fn ($item, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $item['name'],
                    'url' => $seo->absolute($item['url']),
                ])->all(),
            ],
        ];
    }
}
