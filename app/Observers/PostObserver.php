<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\MediaReference;
use App\Plugins\Blog\Models\Post;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PostObserver
{
    /**
     * Handle the Post "saved" event.
     * Erstellt automatisch MediaReferences für alle im Content verwendeten Medien.
     */
    public function saved(Post $post): void
    {
        if (!$post->content) {
            return;
        }

        // Lösche alte Content-Media-Referenzen
        MediaReference::where('model_type', Post::class)
            ->where('model_id', $post->id)
            ->where('collection_name', 'content')
            ->delete();

        // Finde alle Media-URLs im Content
        preg_match_all('/\/storage\/media\/([^"\'>\s]+)/', $post->content, $matches);

        if (empty($matches[1])) {
            return;
        }

        $processedIds = [];

        foreach ($matches[1] as $filename) {
            $media = Media::where('file_name', $filename)->first();

            if ($media && !in_array($media->id, $processedIds)) {
                MediaReference::create([
                    'media_id' => $media->id,
                    'model_type' => Post::class,
                    'model_id' => $post->id,
                    'collection_name' => 'content', // Spezielle Collection für Content-Medien
                ]);

                $processedIds[] = $media->id;
            }
        }
    }

    /**
     * Handle the Post "deleting" event.
     * Entfernt MediaReferences wenn ein Post gelöscht wird.
     */
    public function deleting(Post $post): void
    {
        MediaReference::where('model_type', Post::class)
            ->where('model_id', $post->id)
            ->delete();
    }
}
