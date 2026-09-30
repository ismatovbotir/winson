<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = ['product_id', 'label_uz', 'label_ru', 'value_uz', 'value_ru', 'sort_order'];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'ru' ? $this->label_ru : $this->label_uz);
    }

    protected function value(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'ru' ? $this->value_ru : $this->value_uz);
    }
}
