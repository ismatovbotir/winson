<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\Presenter;
use App\Models\Category;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('list_categories')]
#[Description('All product categories (slug, uz/ru names, product count, intro text, SEO fields, public and admin URLs).')]
class ListCategories extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::json(Category::withCount('products')->orderBy('sort_order')->get()->map(fn ($c) => Presenter::category($c, true)));
    }
}
