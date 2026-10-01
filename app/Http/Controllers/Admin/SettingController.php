<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Contacts;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** Form sections → setting keys, in display order. */
    public const GROUPS = [
        'contacts' => Contacts::KEYS,
        'seo' => [
            'seo_indexing',
            'seo_catalog_title_uz', 'seo_catalog_title_ru',
            'seo_catalog_description_uz', 'seo_catalog_description_ru',
            'seo_news_title_uz', 'seo_news_title_ru',
            'seo_news_description_uz', 'seo_news_description_ru',
            'seo_same_as',
        ],
        'analytics' => [
            'google_analytics_id',
            'yandex_metrica_id',
            'google_site_verification',
            'yandex_site_verification',
        ],
    ];

    public function edit()
    {
        return view('admin.settings.edit', [
            'groups' => collect(self::GROUPS)->map(fn ($keys) => Setting::getMany($keys)),
        ]);
    }

    public function update(Request $request)
    {
        // Accept "@name", "name" or a full t.me link for Telegram; store the bare username.
        if ($request->filled('contact_telegram')) {
            $request->merge(['contact_telegram' => preg_replace(
                '~^(https?://)?(t\.me/|telegram\.me/)?@?~i', '', trim($request->input('contact_telegram'))
            )]);
        }

        $phone = ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ()\-]{7,}$/'];

        $data = $request->validate([
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => $phone,
            'contact_whatsapp' => $phone,
            'contact_telegram' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]{4,}$/'],
            'contact_address_uz' => ['nullable', 'string', 'max:255'],
            'contact_address_ru' => ['nullable', 'string', 'max:255'],
            'seo_catalog_title_uz' => ['nullable', 'string', 'max:255'],
            'seo_catalog_title_ru' => ['nullable', 'string', 'max:255'],
            'seo_catalog_description_uz' => ['nullable', 'string', 'max:500'],
            'seo_catalog_description_ru' => ['nullable', 'string', 'max:500'],
            'seo_news_title_uz' => ['nullable', 'string', 'max:255'],
            'seo_news_title_ru' => ['nullable', 'string', 'max:255'],
            'seo_news_description_uz' => ['nullable', 'string', 'max:500'],
            'seo_news_description_ru' => ['nullable', 'string', 'max:500'],
            'seo_same_as' => ['nullable', 'string', 'max:2000'],
            'google_analytics_id' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9-]+$/i'],
            'yandex_metrica_id' => ['nullable', 'digits_between:1,20'],
            'google_site_verification' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
            'yandex_site_verification' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        // Checkbox: unchecked = search engines blocked (robots.txt Disallow: /).
        $data['seo_indexing'] = $request->boolean('seo_indexing') ? '1' : '0';

        // Social/profile links for Organization.sameAs: one URL per line, http(s) only.
        if (! empty($data['seo_same_as'])) {
            $data['seo_same_as'] = collect(preg_split('/\s+/', $data['seo_same_as']))
                ->filter(fn ($u) => preg_match('~^https?://[^\s]+$~i', $u))->unique()->implode("\n") ?: null;
        }

        foreach (array_merge(...array_values(self::GROUPS)) as $key) {
            Setting::set($key, $data[$key] ?? null);
        }

        \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\SeoController::SITEMAP_CACHE);

        return back()->with('status', __('admin.common.saved'));
    }
}
