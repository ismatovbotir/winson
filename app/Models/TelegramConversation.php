<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramConversation extends Model
{
    protected $fillable = [
        'telegram_client_id', 'locale', 'started_at', 'last_message_at', 'ended_at', 'end_reason',
        'summary', 'interest', 'wants_contact', 'rating', 'lead_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_message_at' => 'datetime',
        'ended_at' => 'datetime',
        'wants_contact' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(TelegramClient::class, 'telegram_client_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class)->orderBy('id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isOpen(): bool
    {
        return $this->ended_at === null;
    }
}
