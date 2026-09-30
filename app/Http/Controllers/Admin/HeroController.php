<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeContent;
use App\Support\SafeUrl;
use Illuminate\Http\Request;

/** The homepage hero block: texts (uz/ru), button links, spark animation. */
class HeroController extends Controller
{
    public const TEXT_FIELDS = ['hero_kicker', 'hero_title', 'hero_subtitle', 'hero_cta_primary', 'hero_cta_secondary'];

    public function edit()
    {
        return view('admin.hero.edit', [
            'content' => HomeContent::current(),
            'fields' => self::TEXT_FIELDS,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'hero_cta_primary_link' => ['nullable', 'string', 'max:500', 'regex:'.SafeUrl::PATTERN],
            'hero_cta_secondary_link' => ['nullable', 'string', 'max:500', 'regex:'.SafeUrl::PATTERN],
        ];
        foreach (self::TEXT_FIELDS as $field) {
            $max = $field === 'hero_subtitle' ? 1000 : 255;
            $rules["{$field}_uz"] = ['nullable', 'string', "max:{$max}"];
            $rules["{$field}_ru"] = ['nullable', 'string', "max:{$max}"];
        }

        $data = $request->validate($rules);
        $data['hero_sparks'] = $request->boolean('hero_sparks');

        HomeContent::current()->update($data);

        return back()->with('status', __('admin.common.saved'));
    }
}
