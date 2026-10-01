<?php

namespace App\Mcp\Support;

use App\Models\Article;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Product;
use App\Support\RichText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Creates/updates catalog content for the MCP write tools with the same rules
 * as the admin forms (validation, RichText sanitizing, slug format).
 * Updates are partial: only keys present in the input change.
 * Returns ['model' => …, 'warnings' => [...]] — warnings explain anything
 * that was skipped (unknown feature code, bad option…) so the AI can fix it.
 */
class ContentWriter
{
    private array $warnings = [];

    // ---------------- categories ----------------

    public function category(array $in, ?Category $c = null): array
    {
        $creating = ! $c;
        $c ??= new Category;
        $in = $this->slugged($in, 'name_uz', $creating);

        $data = $this->validate($in, [
            'slug' => [$creating ? 'required' : 'sometimes', 'alpha_dash', 'max:100', Rule::unique('categories')->ignore($c)],
            'name_uz' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'name_ru' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'description_uz' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'description_ru' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ] + $this->seoRules());

        $c->fill($data);
        $c->sort_order ??= (int) Category::max('sort_order') + 1;
        $c->save();

        return ['model' => $c->fresh(), 'warnings' => $this->warnings];
    }

    // ---------------- products ----------------

    public function product(array $in, ?Product $p = null): array
    {
        $creating = ! $p;
        $p ??= new Product;
        $in = $this->slugged($in, 'name_uz', $creating);

        $data = $this->validate($in, [
            'category' => [$creating ? 'required' : 'sometimes', 'string', 'exists:categories,slug'],
            'slug' => [$creating ? 'required' : 'sometimes', 'alpha_dash', 'max:100', Rule::unique('products')->ignore($p)],
            'name_uz' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'name_ru' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'description_uz' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'description_ru' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'features' => ['sometimes', 'array'],
            'extra_specs' => ['sometimes', 'array', 'max:50'],
            'extra_specs.*.label_uz' => ['required_without:extra_specs.*.label_ru', 'nullable', 'string', 'max:255'],
            'extra_specs.*.label_ru' => ['nullable', 'string', 'max:255'],
            'extra_specs.*.value_uz' => ['nullable', 'string', 'max:255'],
            'extra_specs.*.value_ru' => ['nullable', 'string', 'max:255'],
            'related' => ['sometimes', 'array', 'max:12'],
            'related.*' => ['string'],
        ] + $this->seoRules());

        return DB::transaction(function () use ($p, $data, $creating) {
            if (isset($data['category'])) {
                $p->category_id = Category::where('slug', $data['category'])->value('id');
            }
            $p->fill(collect($data)->except(['category', 'features', 'extra_specs', 'related'])->all());
            if ($creating) {
                $p->sort_order ??= (int) Product::where('category_id', $p->category_id)->max('sort_order') + 1;
            }
            $p->save();

            if (array_key_exists('features', $data)) {
                $this->features($p, $data['features']);
            }

            if (array_key_exists('extra_specs', $data)) {
                $p->specs()->delete();
                foreach (array_values($data['extra_specs']) as $i => $row) {
                    $p->specs()->create([
                        'label_uz' => ($row['label_uz'] ?? null) ?: $row['label_ru'],
                        'label_ru' => ($row['label_ru'] ?? null) ?: $row['label_uz'],
                        'value_uz' => ($row['value_uz'] ?? null) ?: ($row['value_ru'] ?? ''),
                        'value_ru' => ($row['value_ru'] ?? null) ?: ($row['value_uz'] ?? ''),
                        'sort_order' => $i,
                    ]);
                }
            }

            if (array_key_exists('related', $data)) {
                $ids = Product::whereIn('slug', $data['related'])->where('id', '!=', $p->id)->pluck('id', 'slug');
                foreach (array_diff($data['related'], $ids->keys()->all()) as $missing) {
                    $this->warnings[] = "related: product '{$missing}' not found — skipped";
                }
                $p->relatedProducts()->sync($ids->values()->all());
            }

            return ['model' => $p->fresh('category'), 'warnings' => $this->warnings];
        });
    }

    /**
     * features: { code: value } — value by type:
     *   select: option code · multi: [option codes] · boolean: true/false · number: 12.5
     *   null clears that feature. Codes not in the input are left as they are.
     */
    private function features(Product $p, array $input): void
    {
        $features = Feature::with('options')->whereIn('code', array_keys($input))->get()->keyBy('code');

        foreach ($input as $code => $value) {
            $f = $features[$code] ?? null;
            if (! $f) {
                $this->warnings[] = "features.{$code}: unknown feature code (see list_features) — skipped";

                continue;
            }

            $rows = [];
            if ($value === null || $value === [] || $value === '') {
                // clear
            } elseif ($f->hasOptions()) {
                $codes = array_map('strval', (array) $value);
                if ($f->type === 'select' && count($codes) > 1) {
                    $this->warnings[] = "features.{$code}: single-choice feature, used the first value only";
                    $codes = [$codes[0]];
                }
                foreach ($codes as $optionCode) {
                    $option = $f->options->firstWhere('code', $optionCode);
                    if (! $option) {
                        $this->warnings[] = "features.{$code}: unknown option '{$optionCode}' (valid: ".$f->options->pluck('code')->implode(', ').') — skipped';

                        continue;
                    }
                    $rows[] = ['feature_option_id' => $option->id, 'value_number' => null];
                }
                if (! $rows) {
                    continue; // nothing valid: keep the old value rather than wiping it
                }
            } elseif ($f->type === 'boolean') {
                if (! is_bool($value) && ! in_array($value, [0, 1, '0', '1'], true)) {
                    $this->warnings[] = "features.{$code}: expected true/false — skipped";

                    continue;
                }
                $rows[] = ['feature_option_id' => null, 'value_number' => (int) (bool) $value];
            } else {
                if (! is_numeric($value)) {
                    $this->warnings[] = "features.{$code}: expected a number".($f->unit_ru ? " in {$f->unit_ru}" : '').' — skipped';

                    continue;
                }
                $rows[] = ['feature_option_id' => null, 'value_number' => (float) $value];
            }

            $p->featureValues()->where('feature_id', $f->id)->delete();
            foreach ($rows as $row) {
                $p->featureValues()->create($row + ['feature_id' => $f->id]);
            }
        }
    }

    // ---------------- articles ----------------

    public function article(array $in, ?Article $a = null): array
    {
        $creating = ! $a;
        $a ??= new Article;
        $in = $this->slugged($in, 'title_uz', $creating);

        $data = $this->validate($in, [
            'slug' => [$creating ? 'required' : 'sometimes', 'alpha_dash', 'max:150', Rule::unique('articles')->ignore($a)],
            'title_uz' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'title_ru' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'excerpt_uz' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'excerpt_ru' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'body_html_uz' => [$creating ? 'required' : 'sometimes', 'string', 'max:200000'],
            'body_html_ru' => [$creating ? 'required' : 'sometimes', 'string', 'max:200000'],
            'published_at' => ['sometimes', 'date'],
            'read_minutes' => ['sometimes', 'integer', 'min:1', 'max:120'],
        ] + $this->seoRules());

        foreach (['uz', 'ru'] as $l) {
            if (array_key_exists("body_html_{$l}", $data)) {
                $clean = RichText::clean($data["body_html_{$l}"]);
                if (RichText::text($clean) === '') {
                    throw ValidationException::withMessages(["body_html_{$l}" => 'Body is empty after sanitizing (allowed tags: p, h2, h3, strong, em, u, s, ul, ol, li, blockquote, a, br, hr).']);
                }
                if ($clean !== trim($data["body_html_{$l}"])) {
                    $this->warnings[] = "body_html_{$l}: some HTML was removed by the sanitizer (only basic formatting, safe links and site images are kept)";
                }
                $data["body_{$l}"] = $clean;
                unset($data["body_html_{$l}"]);
            }
        }

        $a->fill($data);
        $a->published_at ??= now();
        if (! array_key_exists('read_minutes', $data) && (isset($data['body_uz']) || isset($data['body_ru']))) {
            $words = preg_match_all("/[\\p{L}\\p{N}]+/u", RichText::text($a->body_ru ?: $a->body_uz));
            $a->read_minutes = max(1, (int) ceil($words / 180));
        }
        $a->save();

        return ['model' => $a->fresh(), 'warnings' => $this->warnings];
    }

    // ---------------- helpers ----------------

    private function seoRules(): array
    {
        return [
            'meta_title_uz' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_title_ru' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description_uz' => ['sometimes', 'nullable', 'string', 'max:500'],
            'meta_description_ru' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /** Slug: normalized if given; generated from the uz name/title when creating. */
    private function slugged(array $in, string $from, bool $creating): array
    {
        if (isset($in['slug'])) {
            $in['slug'] = Str::slug($in['slug']);
        } elseif ($creating && ! empty($in[$from])) {
            $in['slug'] = Str::slug($in[$from]);
        }

        return $in;
    }

    private function validate(array $in, array $rules): array
    {
        $v = Validator::make($in, $rules);
        if ($v->fails()) {
            throw new ValidationException($v);
        }

        return $v->validated();
    }
}
