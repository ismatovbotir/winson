<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureOption extends Model
{
    protected $fillable = ['feature_id', 'code', 'label_uz', 'label_ru', 'sort_order'];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'ru' ? $this->label_ru : $this->label_uz);
    }
}
