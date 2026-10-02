<?php

namespace App\Models;

use App\Support\SafeUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    protected $fillable = ['label_uz', 'label_ru', 'url', 'new_tab', 'is_active', 'sort_order'];

    protected $casts = ['new_tab' => 'boolean', 'is_active' => 'boolean'];

    /** Active header links in order, once per request. */
    public static function header(): Collection
    {
        return once(fn () => static::where('is_active', true)->orderBy('sort_order')->get());
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'ru' ? $this->label_ru : $this->label_uz);
    }

    /** Relative paths ("/catalog", "/#about") resolve against the site; full URLs pass through. */
    protected function href(): Attribute
    {
        return Attribute::get(fn () => SafeUrl::href($this->url));
    }
}
