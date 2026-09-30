<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\MenuItem;
use App\Support\SafeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** The header menu is edited as one list and saved wholesale (row order = display order). */
class MenuController extends Controller
{
    public function edit()
    {
        // Suggestions for the URL field (a <datalist>), so admins can pick real pages.
        $suggestions = collect(['/', '/katalog', '/news', '/#about', '/#contact'])
            ->merge(Category::orderBy('sort_order')->pluck('slug')->map(fn ($s) => "/katalog/{$s}"))
            ->merge(Article::orderByDesc('published_at')->pluck('slug')->map(fn ($s) => "/news/{$s}"));

        return view('admin.menu.edit', [
            'items' => MenuItem::orderBy('sort_order')->get(),
            'suggestions' => $suggestions,
        ]);
    }

    public function update(Request $request)
    {
        // Drop fully empty rows (e.g. a blank row added and never filled).
        $rows = collect($request->input('items', []))
            ->filter(fn ($row) => is_array($row) && (trim($row['label_uz'] ?? '') !== '' || trim($row['label_ru'] ?? '') !== '' || trim($row['url'] ?? '') !== ''))
            ->values();
        $request->merge(['items' => $rows->all()]);

        $data = $request->validate([
            'items' => ['array', 'max:12'],
            'items.*.label_uz' => ['required', 'string', 'max:40'],
            'items.*.label_ru' => ['required', 'string', 'max:40'],
            'items.*.url' => ['required', 'string', 'max:500', 'regex:'.SafeUrl::PATTERN],
        ]);

        DB::transaction(function () use ($data, $rows) {
            MenuItem::query()->delete();

            foreach ($data['items'] ?? [] as $i => $row) {
                MenuItem::create([
                    'label_uz' => $row['label_uz'],
                    'label_ru' => $row['label_ru'],
                    'url' => trim($row['url']),
                    'new_tab' => (bool) ($rows[$i]['new_tab'] ?? false),
                    'is_active' => (bool) ($rows[$i]['is_active'] ?? false),
                    'sort_order' => $i,
                ]);
            }
        });

        return back()->with('status', __('admin.common.saved'));
    }
}
