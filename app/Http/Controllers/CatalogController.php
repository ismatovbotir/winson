<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class CatalogController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->withCount('products')->get();

        return view('catalog.index', compact('categories'));
    }

    public function category(Category $category)
    {
        $category->load('products');

        return view('catalog.category', compact('category'));
    }

    public function item(Category $category, Product $product)
    {
        abort_unless($product->category_id === $category->id, 404);

        $product->load('specs', 'images');
        $similar = $product->similar()->load('category');

        return view('catalog.item', compact('category', 'product', 'similar'));
    }
}
