<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;

/** Shared bits for the create_* / update_* input schemas. */
trait SchemaHelpers
{
    private function isUpdate(): bool
    {
        return str_starts_with(class_basename($this), 'Update');
    }

    /** current_slug (update tools only) + a field that is required only on create. */
    private function base(JsonSchema $schema, string $what): array
    {
        return $this->isUpdate()
            ? ['current_slug' => $schema->string()->description("Slug of the {$what} to update.")->required()]
            : [];
    }

    private function req($type)
    {
        return $this->isUpdate() ? $type : $type->required();
    }

    private function seo(JsonSchema $schema): array
    {
        return [
            'meta_title_uz' => $schema->string()->description('SEO <title> (uz), ≤60 chars. Empty = automatic.'),
            'meta_title_ru' => $schema->string()->description('SEO <title> (ru), ≤60 chars. Empty = automatic.'),
            'meta_description_uz' => $schema->string()->description('Meta description (uz), 140–160 chars.'),
            'meta_description_ru' => $schema->string()->description('Meta description (ru), 140–160 chars.'),
        ];
    }
}
