<?php

namespace App\Support;

/**
 * Admin-entered link targets (banner buttons, menu items). Only site-relative
 * paths ("/katalog", "/#about", "#contact") and http(s)/mailto/tel URLs are
 * allowed, so a stored value can never become a `javascript:`/`data:` link
 * or a protocol-relative "//evil.com" redirect.
 */
class SafeUrl
{
    public const PATTERN = '~^(/(?![/\\\\])[^\s]*|#[^\s]*|https?://[^\s/]+[^\s]*|mailto:[^\s]+|tel:\+?[0-9 ()\-]+)$~iu';

    public static function isSafe(?string $value): bool
    {
        return $value !== null && preg_match(self::PATTERN, trim($value)) === 1;
    }

    /** Absolute URL for a stored link, or null if it isn't safe. */
    public static function href(?string $value): ?string
    {
        if (! self::isSafe($value)) {
            return null;
        }

        $value = trim($value);

        return str_starts_with($value, '/') ? url($value) : $value;
    }
}
