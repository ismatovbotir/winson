<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use StoresImages;

    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(Request $request)
    {
        $category = new Category;
        $this->save($request, $category);

        return redirect()->route('admin.categories.index')->with('status', __('admin.common.saved'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->save($request, $category);

        return redirect()->route('admin.categories.index')->with('status', __('admin.common.saved'));
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => __('admin.categories.has_products')]);
        }

        $this->deleteImage($category->image);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', __('admin.common.deleted'));
    }

    private function save(Request $request, Category $category): void
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name_uz'))]);

        $data = $request->validate([
            'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('categories')->ignore($category)],
            'name_uz' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'description_uz' => ['nullable', 'string', 'max:5000'],
            'description_ru' => ['nullable', 'string', 'max:5000'],
            'meta_title_uz' => ['nullable', 'string', 'max:255'],
            'meta_title_ru' => ['nullable', 'string', 'max:255'],
            'meta_description_uz' => ['nullable', 'string', 'max:500'],
            'meta_description_ru' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['sort_order'] ??= 0;
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'), 'categories', $category->image);
        }

        $category->fill($data)->save();
    }
}
