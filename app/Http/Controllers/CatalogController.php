<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\ProductFilter;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->withCount('products')->get();

        return view('catalog.index', compact('categories'));
    }

    public function category(Request $request, Category $category)
    {
        $category->load('products.featureValues.feature', 'products.featureValues.option');

        $filter = new ProductFilter($category->products, (array) $request->query('f', []));

        return view('catalog.category', [
            'category' => $category,
            'filter' => $filter,
            'products' => $filter->results(),
        ]);
    }

    public function item(Category $category, Product $product)
    {
        abort_unless($product->category_id === $category->id, 404);

        $product->load('specs', 'images', 'featureValues.feature', 'featureValues.option');
        $similar = $product->similar()->load('category');

        return view('catalog.item', compact('category', 'product', 'similar'));
    }
}
