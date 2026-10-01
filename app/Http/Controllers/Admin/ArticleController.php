<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ArticleController extends Controller
{
    use StoresImages;

    public function index()
    {
        $articles = Article::withCount(['views as views_30d' => fn ($q) => $q->since(30)])
            ->orderByDesc('published_at')
            ->get();

        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.articles.form', ['article' => new Article([
            'published_at' => now(),
            'read_minutes' => 4,
        ])]);
    }

    public function store(Request $request)
    {
        $article = $this->save($request, new Article);

        // Stay on the form so newly uploaded photos' [rasm:ID] tokens can be placed in the text.
        return redirect()->route('admin.articles.edit', $article)->with('status', __('admin.common.saved'));
    }

    public function edit(Article $article)
    {
        return view('admin.articles.form', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $article = $this->save($request, $article);

        return redirect()->route('admin.articles.edit', $article)->with('status', __('admin.common.saved'));
    }

    public function destroy(Article $article)
    {
        $this->deleteImage($article->image);
        foreach (array_merge(RichText::uploadedImages($article->body_uz), RichText::uploadedImages($article->body_ru)) as $path) {
            $this->deleteImage($path);
        }
        $article->delete();

        return redirect()->route('admin.articles.index')->with('status', __('admin.common.deleted'));
    }

    private function save(Request $request, Article $article): Article
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title_uz'))]);

        $data = $request->validate([
            'slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('articles')->ignore($article)],
            'title_uz' => ['required', 'string', 'max:255'],
            'title_ru' => ['required', 'string', 'max:255'],
            'excerpt_uz' => ['required', 'string', 'max:255'],
            'excerpt_ru' => ['required', 'string', 'max:255'],
            'body_uz' => ['required', 'string', 'max:200000'],
            'body_ru' => ['required', 'string', 'max:200000'],
            'meta_title_uz' => ['nullable', 'string', 'max:255'],
            'meta_title_ru' => ['nullable', 'string', 'max:255'],
            'meta_description_uz' => ['nullable', 'string', 'max:500'],
            'meta_description_ru' => ['nullable', 'string', 'max:500'],
            'published_at' => ['required', 'date'],
            'read_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        // Rich-text bodies: store only sanitized HTML, and it must still have text.
        foreach (['body_uz', 'body_ru'] as $field) {
            $data[$field] = RichText::clean($data[$field]);
            if (RichText::text($data[$field]) === '' && ! str_contains($data[$field], '<img')) {
                throw ValidationException::withMessages([$field => __('validation.required', ['attribute' => __('validation.attributes.'.$field)])]);
            }
        }

        $removedImages = array_diff(
            array_merge(RichText::uploadedImages($article->body_uz), RichText::uploadedImages($article->body_ru)),
            array_merge(RichText::uploadedImages($data['body_uz']), RichText::uploadedImages($data['body_ru'])),
        );

        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'), 'articles', $article->image);
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($article->image);
            $data['image'] = null;
        }

        $article->fill($data)->save();

        // Photos taken out of the text in the editor: delete their files.
        foreach ($removedImages as $path) {
            $this->deleteImage($path);
        }

        return $article;
    }
}
