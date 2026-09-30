<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageView extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'path', 'route_name', 'subject_type', 'subject_id',
        'locale', 'visitor_hash', 'referrer_host',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** Limit to the last $days days (null = all time). */
    public function scopeSince(Builder $query, ?int $days): Builder
    {
        return $days ? $query->where('created_at', '>=', now()->subDays($days - 1)->startOfDay()) : $query;
    }
}
