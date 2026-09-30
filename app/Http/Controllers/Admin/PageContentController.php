<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeContent;
use Illuminate\Http\Request;

class PageContentController extends Controller
{
    /** Field groups as rendered on the form; each field has _uz and _ru columns. */
    public const SECTIONS = [
        'about' => [
            'about_kicker', 'about_title', 'about_body',
            'about_stat1_label', 'about_stat1_value', 'about_stat2_label', 'about_stat2_value',
            'about_stat3_label', 'about_stat3_value', 'about_stat4_label', 'about_stat4_value',
        ],
        'cta' => ['cta_title', 'cta_body', 'cta_button'],
    ];

    /** Fields edited as a textarea rather than a single-line input. */
    public const LONG = ['about_body', 'cta_body'];

    public function edit()
    {
        return view('admin.page-content.edit', [
            'content' => HomeContent::current(),
            'sections' => self::SECTIONS,
            'long' => self::LONG,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (array_merge(...array_values(self::SECTIONS)) as $field) {
            $max = in_array($field, self::LONG, true) ? 2000 : 255;
            $rules["{$field}_uz"] = ['nullable', 'string', "max:{$max}"];
            $rules["{$field}_ru"] = ['nullable', 'string', "max:{$max}"];
        }

        HomeContent::current()->update($request->validate($rules));

        return back()->with('status', __('admin.common.saved'));
    }
}
