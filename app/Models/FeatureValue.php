<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureValue extends Model
{
    protected $fillable = ['product_id', 'feature_id', 'feature_option_id', 'value_number'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(FeatureOption::class, 'feature_option_id');
    }
}
