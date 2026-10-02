<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramMessage extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['telegram_client_id', 'telegram_conversation_id', 'role', 'text'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(TelegramClient::class, 'telegram_client_id');
    }
}
