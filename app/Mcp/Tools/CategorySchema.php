<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;

trait CategorySchema
{
    use SchemaHelpers;

    public function schema(JsonSchema $schema): array
    {
        return $this->base($schema, 'category') + [
            'name_uz' => $this->req($schema->string()->description('Name in Uzbek (Latin).')),
            'name_ru' => $this->req($schema->string()->description('Name in Russian.')),
            'slug' => $schema->string()->description('URL slug (latin, hyphens). Default: from name_uz. Changing it changes the URL.'),
            'description_uz' => $schema->string()->description('Intro text shown on the category page (uz), 100–200 words; blank line between paragraphs.'),
            'description_ru' => $schema->string()->description('Intro text (ru).'),
            'sort_order' => $schema->integer()->description('Position in lists (lower = first).'),
        ] + $this->seo($schema);
    }
}
