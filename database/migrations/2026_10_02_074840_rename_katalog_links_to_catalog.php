<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The catalog moved from /katalog to /catalog (old URLs 301 via
     * LocaleController). Rewrite links admins saved, so visitors skip the hop.
     */
    private const PATH = '~^(/(?:uz|ru))?/(?:katalog|category)(?=/|$|\?|#)~';

    private const HREF = '~href="(/(?:uz|ru))?/(?:katalog|category)(?=[/"?#])~';

    public function up(): void
    {
        $fix = fn (?string $v) => $v === null ? null : preg_replace(self::PATH, '$1/catalog', $v);

        foreach (DB::table('menu_items')->get(['id', 'url']) as $row) {
            DB::table('menu_items')->where('id', $row->id)->update(['url' => $fix($row->url)]);
        }
        foreach (DB::table('banners')->whereNotNull('link')->get(['id', 'link']) as $row) {
            DB::table('banners')->where('id', $row->id)->update(['link' => $fix($row->link)]);
        }
        foreach (DB::table('home_contents')->get(['id', 'hero_cta_primary_link', 'hero_cta_secondary_link']) as $row) {
            DB::table('home_contents')->where('id', $row->id)->update([
                'hero_cta_primary_link' => $fix($row->hero_cta_primary_link),
                'hero_cta_secondary_link' => $fix($row->hero_cta_secondary_link),
            ]);
        }
        foreach (DB::table('articles')->get(['id', 'body_uz', 'body_ru']) as $row) {
            DB::table('articles')->where('id', $row->id)->update([
                'body_uz' => preg_replace(self::HREF, 'href="$1/catalog', (string) $row->body_uz),
                'body_ru' => preg_replace(self::HREF, 'href="$1/catalog', (string) $row->body_ru),
            ]);
        }
    }

    public function down(): void
    {
        // Links stay valid either way (old paths redirect); nothing to undo.
    }
};
