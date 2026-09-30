<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Site-wide technical settings (analytics IDs, search-console verification
 * codes) — not translatable, unlike HomeContent. Simple key/value store.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** @return array<string, string|null> */
    public static function getMany(array $keys): array
    {
        $rows = static::query()->whereIn('key', $keys)->pluck('value', 'key');

        return collect($keys)->mapWithKeys(fn ($k) => [$k => $rows[$k] ?? null])->all();
    }
}
