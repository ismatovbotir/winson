<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Something the chatbot was taught in /admin → Chatbot training:
 * a setting guide (sent verbatim with barcode images) or an FAQ fact.
 */
class BotKnowledge extends Model
{
    public const KINDS = ['guide', 'faq'];

    protected $table = 'bot_knowledge';

    protected $fillable = [
        'kind', 'title_uz', 'title_ru', 'content_uz', 'content_ru', 'keywords',
        'applies_to_all', 'is_active', 'sort_order',
    ];

    protected $casts = ['applies_to_all' => 'boolean', 'is_active' => 'boolean'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'bot_knowledge_product');
    }

    public function images(): HasMany
    {
        return $this->hasMany(BotKnowledgeImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function title(string $locale): string
    {
        return $locale === 'ru' ? $this->title_ru : $this->title_uz;
    }

    public function content(string $locale): string
    {
        return $locale === 'ru' ? $this->content_ru : $this->content_uz;
    }

    /** "WNI-9610, ST10-71" or "all models" — for prompts and admin lists. */
    public function modelsLabel(): string
    {
        return $this->applies_to_all ? 'ALL models' : ($this->products->pluck('name_ru')->implode(', ') ?: '—');
    }
}
