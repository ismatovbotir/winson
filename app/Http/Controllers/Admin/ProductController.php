<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use StoresImages;

    public function index(Request $request)
    {
        $categories = Category::orderBy('sort_order')->get();

        $products = Product::with('category')
            ->withCount(['views as views_30d' => fn ($q) => $q->since(30), 'featureValues'])
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
        $product->load('specs', 'images', 'relatedProducts', 'featureValues');

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
            'features' => Feature::with('options')->orderBy('sort_order')->get()
                ->groupBy('group')
                ->sortBy(fn ($rows, $group) => array_search($group, Feature::GROUPS, true)),
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
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable'],
            'meta_title_uz' => ['nullable', 'string', 'max:255'],
            'meta_title_ru' => ['nullable', 'string', 'max:255'],
            'meta_description_uz' => ['nullable', 'string', 'max:500'],
            'meta_description_ru' => ['nullable', 'string', 'max:500'],
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
                'description_uz', 'description_ru', 'sort_order',
                'meta_title_uz', 'meta_title_ru', 'meta_description_uz', 'meta_description_ru',
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

            $this->syncFeatures($product, $data['features'] ?? []);

            $product->relatedProducts()->sync(
                collect($data['related'] ?? [])->reject(fn ($id) => (int) $id === $product->id)->all()
            );

            return $product;
        });
    }

    /**
     * Replace the product's characteristics with the submitted ones. Option ids
     * must belong to their feature and numbers must be numeric; anything else
     * is ignored rather than stored.
     */
    private function syncFeatures(Product $product, array $input): void
    {
        $features = Feature::with('options')->whereIn('id', array_keys($input))->get()->keyBy('id');
        $rows = [];

        foreach ($input as $featureId => $value) {
            $feature = $features[$featureId] ?? null;
            if (! $feature || $value === null || $value === '' || $value === []) {
                continue;
            }

            if ($feature->hasOptions()) {
                $validIds = $feature->options->pluck('id')->map(fn ($id) => (string) $id)->all();
                $picked = array_intersect(array_map('strval', (array) $value), $validIds);
                if ($feature->type === 'select') {
                    $picked = array_slice($picked, 0, 1);
                }
                foreach ($picked as $optionId) {
                    $rows[] = ['feature_id' => $feature->id, 'feature_option_id' => (int) $optionId, 'value_number' => null];
                }
            } elseif ($feature->type === 'boolean') {
                if (in_array((string) $value, ['0', '1'], true)) {
                    $rows[] = ['feature_id' => $feature->id, 'feature_option_id' => null, 'value_number' => (int) $value];
                }
            } elseif (is_numeric($value)) {
                $rows[] = ['feature_id' => $feature->id, 'feature_option_id' => null, 'value_number' => (float) $value];
            }
        }

        $product->featureValues()->delete();
        $product->featureValues()->createMany($rows);
    }
}
