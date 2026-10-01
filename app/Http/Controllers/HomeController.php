<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeContent;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('home', [
            'home' => HomeContent::current(),
            'banners' => Banner::active()->get(),
            'categories' => Category::orderBy('sort_order')->withCount('products')->get(),
        ]);
    }
}
