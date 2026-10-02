<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BotKnowledgeImage extends Model
{
    protected $fillable = ['bot_knowledge_id', 'path', 'caption_uz', 'caption_ru', 'sort_order'];

    public function knowledge(): BelongsTo
    {
        return $this->belongsTo(BotKnowledge::class, 'bot_knowledge_id');
    }

    public function caption(string $locale): ?string
    {
        return ($locale === 'ru' ? $this->caption_ru : $this->caption_uz) ?: null;
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => Storage::disk('public')->url($this->path));
    }
}
