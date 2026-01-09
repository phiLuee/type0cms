<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Log;

class MediaObserver
{
    /**
     * Handle the Media "deleting" event.
     * Verhindert das Löschen von Medien, die noch via MediaReference verwendet werden.
     */
    public function deleting(Media $media): bool
    {
        // Prüfe ob noch Referenzen existieren
        $referencesCount = $media->references()->count();

        if ($referencesCount > 0) {
            // Logge die Warnung
            Log::warning("Versuch Media #{$media->id} ({$media->file_name}) zu löschen verhindert: {$referencesCount} Referenz(en) existieren noch.", [
                'media_id' => $media->id,
                'file_name' => $media->file_name,
                'references_count' => $referencesCount,
                'model_type' => $media->model_type,
                'model_id' => $media->model_id,
            ]);

            // Verhindere das physische Löschen
            return false;
        }

        // Wenn keine Referenzen mehr existieren, erlaube das Löschen
        Log::info("Media #{$media->id} ({$media->file_name}) wird gelöscht: Keine Referenzen vorhanden.");
        return true;
    }

    /**
     * Handle the Media "updating" event.
     * Wenn model_type/model_id auf null gesetzt werden, prüfe ob Referenzen existieren.
     */
    public function updating(Media $media): void
    {
        // Wenn die Model-Bindung entfernt wird
        if ($media->isDirty(['model_type', 'model_id'])) {
            $willBeUnbound = $media->model_type === null || $media->model_id === null;

            if ($willBeUnbound) {
                $referencesCount = $media->references()->count();

                if ($referencesCount > 0) {
                    Log::info("Media #{$media->id} ({$media->file_name}) Model-Bindung entfernt, aber {$referencesCount} Referenz(en) schützen das Medium vor Löschung.");
                } else {
                    Log::info("Media #{$media->id} ({$media->file_name}) Model-Bindung entfernt und keine Referenzen vorhanden. Medium kann manuell bereinigt werden.");
                }
            }
        }
    }
}
