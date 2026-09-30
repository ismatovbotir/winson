<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = [
        'slug', 'name_uz', 'name_ru', 'icon', 'image', 'sort_order',
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

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->image) {
                return str_starts_with($this->image, 'images/')
                    ? asset($this->image)
                    : Storage::disk('public')->url($this->image);
            }

            // Convention fallback: the 8 seeded categories each have a
            // generated icon at this path even with no `image` column set.
            return asset("images/products/{$this->slug}.svg");
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
