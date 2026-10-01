<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;

trait ArticleSchema
{
    use SchemaHelpers;

    public function schema(JsonSchema $schema): array
    {
        $html = 'Allowed: <p>, <h2>, <h3>, <strong>, <em>, <u>, <s>, <ul>/<ol>/<li>, <blockquote>, <a href>, <br>, <hr>. Start with <p>, use <h2> for sections. No <h1> (the title is the H1).';

        return $this->base($schema, 'article') + [
            'title_uz' => $this->req($schema->string()->description('Title (uz, Latin).')),
            'title_ru' => $this->req($schema->string()->description('Title (ru).')),
            'excerpt_uz' => $this->req($schema->string()->description('One-sentence summary (uz), ≤255 chars; shown in lists and as fallback meta description.')),
            'excerpt_ru' => $this->req($schema->string()->description('One-sentence summary (ru), ≤255 chars.')),
            'body_html_uz' => $this->req($schema->string()->description("Article body HTML (uz). {$html}")),
            'body_html_ru' => $this->req($schema->string()->description("Article body HTML (ru). {$html}")),
            'slug' => $schema->string()->description('URL slug. Default: from title_uz.'),
            'published_at' => $schema->string()->description('Publish date YYYY-MM-DD. Default: today.'),
            'read_minutes' => $schema->integer()->description('Reading time; computed from the text if omitted.'),
        ] + $this->seo($schema);
    }
}
