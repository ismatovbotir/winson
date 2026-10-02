<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A person talking to the bot. Registered = shared their phone number. */
class TelegramClient extends Model
{
    protected $fillable = [
        'telegram_user_id', 'chat_id', 'first_name', 'last_name', 'username',
        'language_code', 'phone', 'registered_at', 'last_seen_at', 'is_blocked',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_blocked' => 'boolean',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class)->orderBy('id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(TelegramConversation::class)->orderByDesc('id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function isRegistered(): bool
    {
        return $this->registered_at !== null;
    }

    public function displayName(): string
    {
        return trim($this->first_name.' '.$this->last_name) ?: ($this->username ? '@'.$this->username : '#'.$this->telegram_user_id);
    }

    /** Create/update from a Telegram "from" user object. */
    public static function fromTelegram(array $from, int $chatId): self
    {
        $client = static::firstOrNew(['telegram_user_id' => $from['id']]);
        $client->fill([
            'chat_id' => $chatId,
            'first_name' => $from['first_name'] ?? null,
            'last_name' => $from['last_name'] ?? null,
            'username' => $from['username'] ?? null,
            'language_code' => $from['language_code'] ?? $client->language_code,
            'last_seen_at' => now(),
        ])->save();

        return $client;
    }
}
