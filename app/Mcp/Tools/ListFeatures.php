<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\Presenter;
use App\Models\Feature;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('list_features')]
#[Description('The product characteristics catalog: every feature code, its type (select = one option code, multi = list of option codes, boolean = true/false, number = number in the given unit), group, unit and allowed option codes. Needed before setting "features" on a product.')]
class ListFeatures extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::json(Feature::with('options')->orderBy('sort_order')->get()->map(fn ($f) => Presenter::feature($f)));
    }
}
