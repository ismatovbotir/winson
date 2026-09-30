<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Support\RichText;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes rich-text editor uploads that no article uses — e.g. an image was
 * inserted but the article was never saved. Files newer than a day are kept
 * so an admin who is still editing doesn't lose them.
 */
class PruneArticleImages extends Command
{
    protected $signature = 'articles:prune-images {--dry-run : Only list what would be deleted}';

    protected $description = 'Delete unused images uploaded through the article editor';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $used = Article::all(['body_uz', 'body_ru'])
            ->flatMap(fn ($a) => array_merge(RichText::uploadedImages($a->body_uz), RichText::uploadedImages($a->body_ru)))
            ->flip();

        $deleted = 0;
        foreach ($disk->files(RichText::UPLOAD_DIR) as $path) {
            if ($used->has($path) || $disk->lastModified($path) > now()->subDay()->getTimestamp()) {
                continue;
            }
            $this->line(($this->option('dry-run') ? 'would delete ' : 'deleted ').$path);
            if (! $this->option('dry-run')) {
                $disk->delete($path);
            }
            $deleted++;
        }

        $this->info("{$deleted} unused image(s).");

        return self::SUCCESS;
    }
}
