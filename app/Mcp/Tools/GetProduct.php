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
#[Name('get_product')]
#[Description('Full product details: uz/ru name and description, characteristics (by feature code and human-readable), extra specs, related products, SEO fields, image info, URLs.')]
class GetProduct extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate(['slug' => 'required|string']);
        $product = Product::with('category')->where('slug', $data['slug'])->first();

        return $product
            ? Response::json(Presenter::product($product, true))
            : Response::error("Product '{$data['slug']}' not found. Use list_products to see slugs.");
    }

    public function schema(JsonSchema $schema): array
    {
        return ['slug' => $schema->string()->description('Product slug, e.g. "wnl-7000g".')->required()];
    }
}
