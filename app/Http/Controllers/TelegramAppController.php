<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Lead;
use App\Models\Product;
use App\Support\Telegram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Telegram Mini App: the catalog inside Telegram (opened from the bot's menu
 * button) with an in-app "request a price" form. Pages live under
 * /tg/{locale}/…; leads are authenticated by Telegram's signed initData.
 */
class TelegramAppController extends Controller
{
    /** /tg — picks uz/ru from the Telegram user's language in JS, then redirects. */
    public function entry()
    {
        return view('tg.entry');
    }

    public function home()
    {
        return view('tg.home', [
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
        ]);
    }

    public function category(Category $category)
    {
        $category->load(['products' => fn ($q) => $q->with('featureValues.feature', 'featureValues.option')]);

        return view('tg.category', compact('category'));
    }

    public function product(Category $category, Product $product)
    {
        abort_unless($product->category_id === $category->id, 404);
        $product->load('images', 'specs', 'featureValues.feature', 'featureValues.option');

        return view('tg.product', compact('category', 'product'));
    }

    /** POST /tg/lead — JSON; only accepted with valid initData from Telegram. */
    public function lead(Request $request): JsonResponse
    {
        $user = Telegram::validateInitData($request->input('init_data'));
        if (! $user) {
            return response()->json(['ok' => false, 'error' => 'telegram_auth'], 403);
        }

        $data = $request->validate([
            'product' => ['nullable', 'integer', 'exists:products,id'],
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^\+?[0-9 ()\-]{5,}$/'],
            'message' => ['nullable', 'string', 'max:1000'],
            'locale' => ['nullable', 'in:uz,ru'],
        ]);

        $lead = Lead::create([
            'source' => 'telegram',
            'product_id' => $data['product'] ?? null,
            'name' => ($data['name'] ?? null) ?: trim(($user['first_name'] ?? '').' '.($user['last_name'] ?? '')) ?: null,
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'telegram_user_id' => $user['id'],
            'telegram_client_id' => \App\Models\TelegramClient::where('telegram_user_id', $user['id'])->value('id'),
            'telegram_username' => $user['username'] ?? null,
            'locale' => $data['locale'] ?? null,
        ]);

        Telegram::notifyLead($lead->load('product.category'));

        return response()->json(['ok' => true]);
    }
}
