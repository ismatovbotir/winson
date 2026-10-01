<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\Presenter;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('list_products')]
#[Description('List products (brief: slug, names, category, description length, number of filled characteristics). Optionally filter by category slug. Use get_product for full details.')]
class ListProducts extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate(['category' => 'sometimes|nullable|string|exists:categories,slug']);

        $products = Product::with('category')
            ->when($data['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->orderBy('category_id')->orderBy('sort_order')->get();

        return Response::json($products->map(fn ($p) => Presenter::product($p)));
    }

    public function schema(JsonSchema $schema): array
    {
        return ['category' => $schema->string()->description('Category slug, e.g. "handheld". Omit for all products.')];
    }
}
