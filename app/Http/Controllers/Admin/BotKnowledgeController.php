<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Models\BotKnowledge;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** /admin → Chatbot training → Knowledge: setting guides (with barcode images) and FAQ facts. */
class BotKnowledgeController extends Controller
{
    use StoresImages;

    public function index(Request $request)
    {
        $kind = in_array($request->query('kind'), BotKnowledge::KINDS, true) ? $request->query('kind') : null;

        return view('admin.bot.knowledge.index', [
            'items' => BotKnowledge::with('products')->withCount('images')
                ->when($kind, fn ($q) => $q->where('kind', $kind))
                ->orderBy('sort_order')->orderByDesc('id')->get(),
            'kind' => $kind,
        ]);
    }

    public function create(Request $request)
    {
        $item = new BotKnowledge([
            'kind' => in_array($request->query('kind'), BotKnowledge::KINDS, true) ? $request->query('kind') : 'guide',
            'is_active' => true,
        ]);

        return view('admin.bot.knowledge.form', ['item' => $item, 'products' => $this->products()]);
    }

    public function store(Request $request)
    {
        $item = $this->save($request, new BotKnowledge);

        return redirect()->route('admin.bot-knowledge.edit', $item)->with('status', __('admin.common.saved'));
    }

    public function edit(BotKnowledge $botKnowledge)
    {
        $botKnowledge->load('products', 'images');

        return view('admin.bot.knowledge.form', ['item' => $botKnowledge, 'products' => $this->products()]);
    }

    public function update(Request $request, BotKnowledge $botKnowledge)
    {
        $this->save($request, $botKnowledge);

        return redirect()->route('admin.bot-knowledge.edit', $botKnowledge)->with('status', __('admin.common.saved'));
    }

    public function destroy(BotKnowledge $botKnowledge)
    {
        $botKnowledge->images->each(fn ($i) => $this->deleteImage($i->path));
        $botKnowledge->delete();

        return redirect()->route('admin.bot-knowledge.index')->with('status', __('admin.common.deleted'));
    }

    private function products()
    {
        return Product::with('category')->orderBy('category_id')->orderBy('sort_order')->get();
    }

    private function save(Request $request, BotKnowledge $item): BotKnowledge
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(BotKnowledge::KINDS)],
            'title_uz' => ['required', 'string', 'max:255'],
            'title_ru' => ['required', 'string', 'max:255'],
            'content_uz' => ['required', 'string', 'max:5000'],
            'content_ru' => ['required', 'string', 'max:5000'],
            'keywords' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'products' => ['nullable', 'array'],
            'products.*' => ['integer', 'exists:products,id'],
            'images' => ['nullable', 'array'],
            'images.*.caption_uz' => ['nullable', 'string', 'max:255'],
            'images.*.caption_ru' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'images.*.remove' => ['nullable', 'boolean'],
            'new_images' => ['nullable', 'array', 'max:20'],
            'new_images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        return DB::transaction(function () use ($request, $item, $data) {
            $item->fill([
                'kind' => $data['kind'],
                'title_uz' => $data['title_uz'],
                'title_ru' => $data['title_ru'],
                'content_uz' => $data['content_uz'],
                'content_ru' => $data['content_ru'],
                'keywords' => $data['keywords'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'applies_to_all' => $request->boolean('applies_to_all'),
                'is_active' => $request->boolean('is_active'),
            ])->save();

            $item->products()->sync($item->applies_to_all ? [] : ($data['products'] ?? []));

            // Existing images: captions, order, removal.
            foreach ($item->images()->get() as $image) {
                $row = $data['images'][$image->id] ?? null;
                if (! $row) {
                    continue;
                }
                if (! empty($row['remove'])) {
                    $this->deleteImage($image->path);
                    $image->delete();

                    continue;
                }
                $image->update([
                    'caption_uz' => $row['caption_uz'] ?? null,
                    'caption_ru' => $row['caption_ru'] ?? null,
                    'sort_order' => $row['sort_order'] ?? $image->sort_order,
                ]);
            }

            // New barcode images: appended in the order they were picked.
            $next = (int) $item->images()->max('sort_order') + 1;
            foreach ($request->file('new_images', []) as $file) {
                $item->images()->create(['path' => $file->store('bot-knowledge', 'public'), 'sort_order' => $next++]);
            }

            return $item;
        });
    }
}
