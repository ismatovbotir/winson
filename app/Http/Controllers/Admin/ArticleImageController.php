<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\RichText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Upload endpoint for images inserted in the article rich-text editor.
 * Returns a site-relative URL, which RichText::clean() accepts as an image src.
 */
class ArticleImageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $path = $request->file('image')->store(RichText::UPLOAD_DIR, 'public');

        return response()->json(['url' => '/storage/'.$path]);
    }
}
