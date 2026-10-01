<?php

namespace App\Models;

use App\Models\Concerns\HasSeoFields;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasSeoFields;

    protected static function booted(): void
    {
        // Content changed → the cached sitemap is stale.
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\SeoController::SITEMAP_CACHE));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\SeoController::SITEMAP_CACHE));
    }

    protected $fillable = [
        'slug', 'name_uz', 'name_ru', 'description_uz', 'description_ru', 'icon', 'image', 'sort_order',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order');
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

            // Convention fallback: the seeded categories each have an
            // illustration named after their slug; anything newer gets a generic one.
            $path = "images/products/{$this->slug}.svg";

            return asset(file_exists(public_path($path)) ? $path : 'images/products/generic.svg');
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
