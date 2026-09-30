<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    protected $fillable = [
        'slug', 'image', 'published_at', 'read_minutes',
        'title_uz', 'title_ru', 'excerpt_uz', 'excerpt_ru', 'body_uz', 'body_ru',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function title(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'ru' ? $this->title_ru : $this->title_uz);
    }

    protected function excerpt(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'ru' ? $this->excerpt_ru : $this->excerpt_uz);
    }

    protected function body(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'ru' ? $this->body_ru : $this->body_uz);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->image) {
                return null;
            }

            return str_starts_with($this->image, 'images/')
                ? asset($this->image)
                : Storage::disk('public')->url($this->image);
        });
    }

    /** Public page views (see TrackPageView). */
    public function views(): MorphMany
    {
        return $this->morphMany(PageView::class, 'subject');
    }
}
