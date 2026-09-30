<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Support\Articles;
use Illuminate\Database\Seeder;

/**
 * Loads the original hand-written articles from App\Support\Articles into
 * the DB. After seeding, the `articles` table is the source of truth.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Articles::all() as $article) {
            Article::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'image' => $article['image'],
                    'published_at' => $article['published_at'],
                    'read_minutes' => $article['read_minutes'],
                    'title_uz' => $article['uz']['title'],
                    'title_ru' => $article['ru']['title'],
                    'excerpt_uz' => $article['uz']['excerpt'],
                    'excerpt_ru' => $article['ru']['excerpt'],
                    'body_uz' => $this->html($article['uz']['body']),
                    'body_ru' => $this->html($article['ru']['body']),
                ]
            );
        }
    }

    /** Plain-text paragraphs → the HTML the rich-text editor stores. */
    private function html(array $paragraphs): string
    {
        return collect($paragraphs)->map(fn ($p) => '<p>'.e($p).'</p>')->implode('');
    }
}
