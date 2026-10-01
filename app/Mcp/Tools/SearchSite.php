<?php

namespace App\Mcp\Tools;

use App\Support\SiteSearch;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('search_site')]
#[Description('Run the same search visitors use (uz and ru, model numbers without dashes, characteristics). Useful to check what a visitor finds for a query, or to find existing content before creating something new.')]
class SearchSite extends Tool
{
    public function handle(Request $request, SiteSearch $search): Response
    {
        $data = $request->validate(['query' => 'required|string|max:100']);
        $r = $search->search($data['query'], 10);

        return Response::json([
            'query' => $data['query'],
            'total' => $r['total'],
            'categories' => $r['categories']->map(fn ($i) => ['title' => $i['title'], 'url' => $i['url']]),
            'products' => $r['products']->map(fn ($i) => ['title' => $i['title'], 'category' => $i['meta'], 'url' => $i['url']]),
            'articles' => $r['articles']->map(fn ($i) => ['title' => $i['title'], 'date' => $i['meta'], 'url' => $i['url']]),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return ['query' => $schema->string()->description('Search text, e.g. "bluetooth skaner" or "wnl7000".')->required()];
    }
}
