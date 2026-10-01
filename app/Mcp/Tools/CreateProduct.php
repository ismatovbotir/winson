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

#[Name('create_product')]
#[Description('Create a product in a category. Required: category (slug), name_uz, name_ru. Optional: descriptions, features {code: value} (see list_features), extra_specs, related (product slugs), SEO fields. Never invent specs — only use facts you were given. Photos are uploaded later in the admin.')]
class CreateProduct extends Tool
{
    use WritesContent, ProductSchema;

    public function handle(Request $request, ContentWriter $writer): Response
    {
        $input = $request->all();

        $result = $writer->product($input);

        return Response::json([
            'saved' => Presenter::product($result['model'], true),
            'warnings' => $result['warnings'],
        ]);
    }
}
