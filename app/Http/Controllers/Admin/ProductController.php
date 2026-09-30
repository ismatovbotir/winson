<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use StoresImages;

    public const SENSOR_TYPES = ['ccd', 'cmos', 'laser'];

    public function index(Request $request)
    {
        $categories = Category::orderBy('sort_order')->get();

        $products = Product::with('category')
            ->withCount(['views as views_30d' => fn ($q) => $q->since(30)])
            ->when($request->integer('category'), fn ($q, $id) => $q->where('category_id', $id))
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(Request $request)
    {
        $product = new Product(['category_id' => $request->integer('category') ?: null]);

        return view('admin.products.form', $this->formData($product));
    }

    public function store(Request $request)
    {
        $product = $this->save($request, new Product);

        return redirect()->route('admin.products.edit', $product)->with('status', __('admin.common.saved'));
    }

    public function edit(Product $product)
    {
        $product->load('specs', 'images', 'relatedProducts');

        return view('admin.products.form', $this->formData($product));
    }

    public function update(Request $request, Product $product)
    {
        $this->save($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('status', __('admin.common.saved'));
    }

    public function destroy(Product $product)
    {
        $this->deleteImage($product->image);
        $product->images->each(fn ($image) => $this->deleteImage($image->path));
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', __('admin.common.deleted'));
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::orderBy('sort_order')->get(),
            'sensorTypes' => self::SENSOR_TYPES,
            'allProducts' => Product::with('category')
                ->when($product->exists, fn ($q) => $q->where('id', '!=', $product->id))
                ->orderBy('category_id')
                ->orderBy('sort_order')
                ->get(),
        ];
    }

    private function save(Request $request, Product $product): Product
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name_uz'))]);

        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('products')->ignore($product)],
            'name_uz' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'description_uz' => ['nullable', 'string', 'max:5000'],
            'description_ru' => ['nullable', 'string', 'max:5000'],
            'sensor_type' => ['nullable', Rule::in(self::SENSOR_TYPES)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'specs' => ['nullable', 'array'],
            'specs.*.label_uz' => ['nullable', 'string', 'max:255'],
            'specs.*.label_ru' => ['nullable', 'string', 'max:255'],
            'specs.*.value_uz' => ['nullable', 'string', 'max:255'],
            'specs.*.value_ru' => ['nullable', 'string', 'max:255'],
            'related' => ['nullable', 'array'],
            'related.*' => ['integer', 'exists:products,id'],
        ]);

        return DB::transaction(function () use ($request, $product, $data) {
            $fields = collect($data)->only([
                'category_id', 'slug', 'name_uz', 'name_ru',
                'description_uz', 'description_ru', 'sensor_type', 'sort_order',
            ])->all();
            $fields['sort_order'] ??= 0;

            if ($request->hasFile('image')) {
                $fields['image'] = $this->storeImage($request->file('image'), 'products', $product->image);
            }

            $product->fill($fields)->save();

            // Spec rows: replace wholesale. A row is kept if any of its four
            // cells has text; missing translations fall back to the other locale.
            $product->specs()->delete();
            collect($data['specs'] ?? [])
                ->map(fn ($row) => collect(['label_uz', 'label_ru', 'value_uz', 'value_ru'])
                    ->mapWithKeys(fn ($key) => [$key => trim((string) ($row[$key] ?? ''))])
                    ->all())
                ->filter(fn ($row) => $row['label_uz'] !== '' || $row['label_ru'] !== '')
                ->values()
                ->each(fn ($row, $i) => $product->specs()->create([
                    'label_uz' => $row['label_uz'] ?: $row['label_ru'],
                    'label_ru' => $row['label_ru'] ?: $row['label_uz'],
                    'value_uz' => $row['value_uz'] ?: $row['value_ru'],
                    'value_ru' => $row['value_ru'] ?: $row['value_uz'],
                    'sort_order' => $i,
                ]));

            $product->images()
                ->whereIn('id', $data['remove_images'] ?? [])
                ->get()
                ->each(function ($image) {
                    $this->deleteImage($image->path);
                    $image->delete();
                });

            $next = (int) $product->images()->max('sort_order') + 1;
            foreach ($request->file('gallery', []) as $file) {
                $product->images()->create([
                    'path' => $file->store('products/gallery', 'public'),
                    'sort_order' => $next++,
                ]);
            }

            $product->relatedProducts()->sync(
                collect($data['related'] ?? [])->reject(fn ($id) => (int) $id === $product->id)->all()
            );

            return $product;
        });
    }
}
