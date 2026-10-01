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

#[Name('create_article')]
#[Description('Create a news/blog article in BOTH languages. Required: title_uz/ru, excerpt_uz/ru (≤255 chars), body_html_uz/ru. Body HTML allows p, h2, h3, strong, em, u, s, ul, ol, li, blockquote, a, br, hr (anything else is stripped). Published immediately (published_at = today unless given).')]
class CreateArticle extends Tool
{
    use WritesContent, ArticleSchema;

    public function handle(Request $request, ContentWriter $writer): Response
    {
        $input = $request->all();

        $result = $writer->article($input);

        return Response::json([
            'saved' => Presenter::article($result['model'], true),
            'warnings' => $result['warnings'],
        ]);
    }
}
