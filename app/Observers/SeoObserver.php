<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\SeoMetadata;

class SeoObserver
{

    /**
     * Handle the SeoMetadata "saving" event (BEFORE save).
     * Verarbeitet og_image Uploads.
     */
    public function saving(SeoMetadata $seo): void
    {
        // WICHTIG: Entferne og_image IMMER aus den Attributen,
        // da es keine DB-Spalte gibt
        unset($seo->og_image);
    }

    /**
     * Handle the SeoMetadata "deleting" event.
     * Entfernt MediaReferences wenn SeoMetadata gelöscht wird.
     */
    public function deleting(SeoMetadata $seo): void
    {
        // Entferne alle Media-Attachments
        $ogImage = $seo->getFirstMedia('og_image');
        if ($ogImage) {
            $seo->detachMedia($ogImage, 'og_image');
        }
    }
}
