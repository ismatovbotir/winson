<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A "request a price" submission (see App\Support\Telegram::notifyLead). */
class Lead extends Model
{
    public const STATUSES = ['new', 'in_progress', 'done'];

    protected $fillable = ['source', 'product_id', 'telegram_client_id', 'name', 'phone', 'message', 'telegram_user_id', 'telegram_username', 'locale', 'status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(TelegramClient::class, 'telegram_client_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
