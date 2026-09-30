<?php

namespace App\Http\Controllers;

use App\Models\Article;

class NewsController extends Controller
{
    public function index()
    {
        $articles = Article::orderByDesc('published_at')->get();

        return view('news.index', compact('articles'));
    }

    public function show(Article $article)
    {
        $others = Article::where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('news.show', compact('article', 'others'));
    }
}
