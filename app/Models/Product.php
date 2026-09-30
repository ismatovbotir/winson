<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'slug', 'name_uz', 'name_ru',
        'description_uz', 'description_ru', 'sensor_type', 'image', 'sort_order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_related', 'product_id', 'related_product_id');
    }

    /** Curated related products if any were picked, else other items in the same category. */
    public function similar(int $limit = 4)
    {
        $related = $this->relatedProducts()->limit($limit)->get();

        if ($related->isNotEmpty()) {
            return $related;
        }

        return Product::where('category_id', $this->category_id)
            ->where('id', '!=', $this->id)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    protected function name(): Attribute
    {
        return Attribute::get(
            fn () => app()->getLocale() === 'ru' ? $this->name_ru : $this->name_uz
        );
    }

    protected function description(): Attribute
    {
        return Attribute::get(
            fn () => app()->getLocale() === 'ru' ? $this->description_ru : $this->description_uz
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->image) {
                return str_starts_with($this->image, 'images/')
                    ? asset($this->image)
                    : Storage::disk('public')->url($this->image);
            }

            return $this->category?->image_url;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Public page views (see TrackPageView). */
    public function views(): MorphMany
    {
        return $this->morphMany(PageView::class, 'subject');
    }
}
