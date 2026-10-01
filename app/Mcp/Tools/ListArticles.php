<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\Presenter;
use App\Models\Article;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('list_articles')]
#[Description('All news/blog articles, newest first (slug, uz/ru titles, publish date, word counts, URLs). Use get_article for the full text.')]
class ListArticles extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::json(Article::orderByDesc('published_at')->get()->map(fn ($a) => Presenter::article($a)));
    }
}
