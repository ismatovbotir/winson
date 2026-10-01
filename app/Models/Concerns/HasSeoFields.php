<?php

namespace App\Models\Concerns;

/** meta_title_{uz,ru} / meta_description_{uz,ru} overrides, resolved to the current locale. */
trait HasSeoFields
{
    public const SEO_FIELDS = ['meta_title_uz', 'meta_title_ru', 'meta_description_uz', 'meta_description_ru'];

    public function initializeHasSeoFields(): void
    {
        $this->mergeFillable(self::SEO_FIELDS);
    }

    public function metaTitle(): ?string
    {
        return $this->getAttribute('meta_title_'.(app()->getLocale() === 'ru' ? 'ru' : 'uz')) ?: null;
    }

    public function metaDescription(): ?string
    {
        return $this->getAttribute('meta_description_'.(app()->getLocale() === 'ru' ? 'ru' : 'uz')) ?: null;
    }
}
