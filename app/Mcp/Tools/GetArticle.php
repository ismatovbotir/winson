<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\Presenter;
use App\Models\Article;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('get_article')]
#[Description('Full article: uz/ru title, excerpt, body HTML, word counts, SEO fields, URLs.')]
class GetArticle extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate(['slug' => 'required|string']);
        $article = Article::where('slug', $data['slug'])->first();

        return $article
            ? Response::json(Presenter::article($article, true))
            : Response::error("Article '{$data['slug']}' not found. Use list_articles to see slugs.");
    }

    public function schema(JsonSchema $schema): array
    {
        return ['slug' => $schema->string()->description('Article slug.')->required()];
    }
}
