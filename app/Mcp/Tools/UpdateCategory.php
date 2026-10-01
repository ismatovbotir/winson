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
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
#[Name('update_category')]
#[Description('Update a category by its current slug. Only the fields you send change (partial update).')]
class UpdateCategory extends Tool
{
    use WritesContent, CategorySchema;

    public function handle(Request $request, ContentWriter $writer): Response
    {
        $input = $request->all();
        $model = Category::where('slug', $input['current_slug'] ?? null)->first();
        if (! $model) {
            return Response::error("Category '".($input['current_slug'] ?? '')."' not found. Use list_categories.");
        }
        unset($input['current_slug']);
        $result = $writer->category($input, $model);

        return Response::json([
            'saved' => Presenter::category($result['model'], true),
            'warnings' => $result['warnings'],
        ]);
    }
}
