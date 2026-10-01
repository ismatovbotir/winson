<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\ContentWriter;
use App\Mcp\Support\Presenter;
use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create_category')]
#[Description('Create a product category (uz + ru names required; optional intro texts, SEO fields). Slug is generated from name_uz if omitted. The image is added later in the admin.')]
class CreateCategory extends Tool
{
    use WritesContent, CategorySchema;

    public function handle(Request $request, ContentWriter $writer): Response
    {
        $input = $request->all();

        $result = $writer->category($input);

        return Response::json([
            'saved' => Presenter::category($result['model'], true),
            'warnings' => $result['warnings'],
        ]);
    }
}
