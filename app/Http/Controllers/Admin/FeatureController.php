<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Admin CRUD for product characteristics (Feature) and their options. */
class FeatureController extends Controller
{
    public function index()
    {
        $features = Feature::withCount(['options', 'values'])->orderBy('sort_order')->get()->groupBy('group');

        return view('admin.features.index', compact('features'));
    }

    public function create()
    {
        return view('admin.features.form', ['feature' => new Feature([
            'type' => 'select', 'group' => 'scanning', 'is_filterable' => true,
            'sort_order' => (int) Feature::max('sort_order') + 1,
        ])]);
    }

    public function store(Request $request)
    {
        $feature = $this->save($request, new Feature);

        return redirect()->route('admin.features.edit', $feature)->with('status', __('admin.common.saved'));
    }

    public function edit(Feature $feature)
    {
        $feature->load('options');

        return view('admin.features.form', compact('feature'));
    }

    public function update(Request $request, Feature $feature)
    {
        $this->save($request, $feature);

        return redirect()->route('admin.features.edit', $feature)->with('status', __('admin.common.saved'));
    }

    public function destroy(Feature $feature)
    {
        $feature->delete(); // options + product values cascade

        return redirect()->route('admin.features.index')->with('status', __('admin.common.deleted'));
    }

    private function save(Request $request, Feature $feature): Feature
    {
        $request->merge(['code' => Str::slug($request->input('code') ?: $request->input('name_uz'), '_')]);

        $data = $request->validate([
            'code' => ['required', 'regex:/^[a-z0-9_]+$/', 'max:60', Rule::unique('features')->ignore($feature)],
            'group' => ['required', Rule::in(Feature::GROUPS)],
            // The type can't change once products use the feature (values would no longer fit).
            'type' => [$feature->exists ? 'nullable' : 'required', Rule::in(Feature::TYPES)],
            'name_uz' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'unit_uz' => ['nullable', 'string', 'max:30'],
            'unit_ru' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array', 'max:200'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.label_uz' => ['nullable', 'string', 'max:255'],
            'options.*.label_ru' => ['nullable', 'string', 'max:255'],
        ]);

        if ($feature->exists) {
            $data['type'] = $feature->type;
        }
        $data['is_filterable'] = $request->boolean('is_filterable');
        $data['sort_order'] ??= 0;
        $options = $data['options'] ?? [];
        unset($data['options']);

        return DB::transaction(function () use ($feature, $data, $options) {
            $feature->fill($data)->save();

            if (! $feature->hasOptions()) {
                return $feature;
            }

            // Options: update by id, create new rows, delete the ones removed in the form
            // (deleting an option also removes it from products — FK cascade).
            $keep = [];
            $rows = collect($options)
                ->map(fn ($r) => ['id' => $r['id'] ?? null, 'label_uz' => trim($r['label_uz'] ?? ''), 'label_ru' => trim($r['label_ru'] ?? '')])
                ->filter(fn ($r) => $r['label_uz'] !== '' || $r['label_ru'] !== '')
                ->values();

            foreach ($rows as $i => $row) {
                $attrs = [
                    'label_uz' => $row['label_uz'] ?: $row['label_ru'],
                    'label_ru' => $row['label_ru'] ?: $row['label_uz'],
                    'sort_order' => $i,
                ];
                $option = $row['id'] ? $feature->options()->find($row['id']) : null;
                if ($option) {
                    $option->update($attrs);
                } else {
                    $base = Str::slug($attrs['label_uz'], '_') ?: 'option';
                    $code = $base;
                    for ($n = 2; $feature->options()->where('code', $code)->exists(); $n++) {
                        $code = $base.'_'.$n;
                    }
                    $option = $feature->options()->create($attrs + ['code' => $code]);
                }
                $keep[] = $option->id;
            }

            $feature->options()->whereNotIn('id', $keep)->delete();

            return $feature;
        });
    }
}
