<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\ContentWriter;
use App\Mcp\Support\Presenter;
use App\Models\Article;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
#[Name('update_article')]
#[Description('Update an article by its current slug. Partial: only sent fields change. Body HTML is sanitized like on create.')]
class UpdateArticle extends Tool
{
    use WritesContent, ArticleSchema;

    public function handle(Request $request, ContentWriter $writer): Response
    {
        $input = $request->all();
        $model = Article::where('slug', $input['current_slug'] ?? null)->first();
        if (! $model) {
            return Response::error("Article '".($input['current_slug'] ?? '')."' not found. Use list_articles.");
        }
        unset($input['current_slug']);
        $result = $writer->article($input, $model);

        return Response::json([
            'saved' => Presenter::article($result['model'], true),
            'warnings' => $result['warnings'],
        ]);
    }
}
