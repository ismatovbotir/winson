<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('write_article')]
#[Description('House rules + workflow for writing a new Winson news/blog article in Uzbek and Russian, then publishing it with create_article.')]
class WriteArticle extends Prompt
{
    public function handle(Request $request): Response
    {
        $topic = trim((string) $request->get('topic', '')) ?: 'a topic you choose from find_content_gaps / get_statistics search queries';
        $audience = trim((string) $request->get('audience', '')) ?: 'owners and IT staff of shops, warehouses, pharmacies and logistics companies in Uzbekistan';

        return Response::text(<<<TEXT
            Write a Winson knowledge-base article about: {$topic}
            Audience: {$audience}.

            Workflow:
            1. Call list_articles and search_site first — don't duplicate an existing article; link to related ones instead.
            2. Use list_products / get_product to mention real Winson models where relevant (link with their URLs). Never invent specs, prices, stock, certifications or delivery terms.
            3. Write the article in BOTH languages as equivalent texts (not word-for-word): Uzbek in Latin script with correct oʻ/gʻ, Russian in natural business style.
            4. 700–1200 words per language. Structure: short intro <p>, 3–5 <h2> sections, lists where useful, a closing <p> that invites the reader to request a price/consultation (no prices).
            5. Excerpt: one sentence ≤ 200 characters per language. meta_title ≤ 60 characters, meta_description 140–160 characters, both languages, with the main search phrase people in Uzbekistan would type.
            6. Publish with create_article (body_html_uz / body_html_ru). Report back the URLs and any warnings.
            TEXT);
    }

    public function arguments(): array
    {
        return [
            new Argument(name: 'topic', description: 'What the article is about, e.g. "how to choose a scanner for a pharmacy".', required: false),
            new Argument(name: 'audience', description: 'Who it is for (optional).', required: false),
        ];
    }
}
