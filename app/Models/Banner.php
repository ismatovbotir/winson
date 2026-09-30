<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Support\SafeUrl;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'image', 'title_uz', 'title_ru', 'text_uz', 'text_ru',
        'button_uz', 'button_ru', 'link', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** Current-locale value of a translatable field, e.g. ->t('title'). */
    public function t(string $field): ?string
    {
        $locale = app()->getLocale() === 'ru' ? 'ru' : 'uz';

        return $this->getAttribute("{$field}_{$locale}");
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image
            ? (str_starts_with($this->image, 'images/') ? asset($this->image) : Storage::disk('public')->url($this->image))
            : null);
    }

    /** Relative paths ("/katalog/pda") resolve against the site; full URLs pass through. */
    protected function href(): Attribute
    {
        return Attribute::get(fn () => SafeUrl::href($this->link));
    }
}
