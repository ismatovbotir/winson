<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Uploads go to the `public` disk. Paths starting with `images/` are the
 * seeded static placeholders under public/images and are never deleted.
 */
trait StoresImages
{
    protected function storeImage(UploadedFile $file, string $dir, ?string $old = null): string
    {
        $this->deleteImage($old);

        return $file->store($dir, 'public');
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'images/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
