<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\ContentWriter;
use App\Mcp\Support\Presenter;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
#[Name('update_product')]
#[Description('Update a product by its current slug. Partial: only sent fields change. "features" only touches the codes you send (null clears one); "extra_specs" and "related", if sent, replace the whole list.')]
class UpdateProduct extends Tool
{
    use WritesContent, ProductSchema;

    public function handle(Request $request, ContentWriter $writer): Response
    {
        $input = $request->all();
        $model = Product::where('slug', $input['current_slug'] ?? null)->first();
        if (! $model) {
            return Response::error("Product '".($input['current_slug'] ?? '')."' not found. Use list_products.");
        }
        unset($input['current_slug']);
        $result = $writer->product($input, $model);

        return Response::json([
            'saved' => Presenter::product($result['model'], true),
            'warnings' => $result['warnings'],
        ]);
    }
}
