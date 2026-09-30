<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Article bodies move from a JSON array of plain-text paragraphs to
     * sanitized HTML written in the admin rich-text editor. Inline photos now
     * live directly in that HTML, so the [rasm:ID] article_images table goes.
     */
    public function up(): void
    {
        $toHtml = fn (?string $json) => collect(json_decode((string) $json, true) ?: [])
            ->filter(fn ($p) => is_string($p) && trim($p) !== '' && ! preg_match('/^\[(?:rasm|image|img|фото|foto):\d+\]$/iu', trim($p)))
            ->map(fn ($p) => '<p>'.htmlspecialchars(trim($p), ENT_QUOTES | ENT_HTML5, 'UTF-8').'</p>')
            ->implode('');

        Schema::table('articles', function (Blueprint $table) {
            $table->longText('body_uz_html')->nullable();
            $table->longText('body_ru_html')->nullable();
        });

        foreach (DB::table('articles')->get(['id', 'body_uz', 'body_ru']) as $row) {
            DB::table('articles')->where('id', $row->id)->update([
                'body_uz_html' => $toHtml($row->body_uz),
                'body_ru_html' => $toHtml($row->body_ru),
            ]);
        }

        Schema::table('articles', fn (Blueprint $table) => $table->dropColumn(['body_uz', 'body_ru']));
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('body_uz_html', 'body_uz');
            $table->renameColumn('body_ru_html', 'body_ru');
        });

        Schema::dropIfExists('article_images');
    }

    public function down(): void
    {
        Schema::create('article_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption_uz')->nullable();
            $table->string('caption_ru')->nullable();
            $table->string('credit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $toJson = fn (?string $html) => json_encode(collect(preg_split('~</(?:p|h2|h3|li|blockquote)>~u', (string) $html))
            ->map(fn ($p) => trim(html_entity_decode(strip_tags($p), ENT_QUOTES | ENT_HTML5, 'UTF-8')))
            ->filter()->values()->all(), JSON_UNESCAPED_UNICODE);

        Schema::table('articles', function (Blueprint $table) {
            $table->json('body_uz_json')->nullable();
            $table->json('body_ru_json')->nullable();
        });
        foreach (DB::table('articles')->get(['id', 'body_uz', 'body_ru']) as $row) {
            DB::table('articles')->where('id', $row->id)->update([
                'body_uz_json' => $toJson($row->body_uz),
                'body_ru_json' => $toJson($row->body_ru),
            ]);
        }
        Schema::table('articles', fn (Blueprint $table) => $table->dropColumn(['body_uz', 'body_ru']));
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('body_uz_json', 'body_uz');
            $table->renameColumn('body_ru_json', 'body_ru');
        });
    }
};
