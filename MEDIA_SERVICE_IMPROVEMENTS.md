# MediaService Best Practices - Implementierte Verbesserungen

## ✅ Implementierte Verbesserungen

### 1. **Custom Exceptions** 
Erstellt in `app/Exceptions/Media/`:
- `MediaException` - Basis-Exception
- `MediaNotFoundException` - Medium nicht gefunden
- `MediaInUseException` - Medium kann nicht gelöscht werden (wird noch verwendet)
- `InvalidMediaFileException` - Ungültige Datei (MIME-Type, Größe)
- `MediaStorageException` - Storage/Dateisystem-Fehler

### 2. **Events**
Erstellt in `app/Events/Media/`:
- `MediaUploaded` - Wird nach erfolgreichem Upload gefeuert
- `MediaDeleted` - Wird nach Löschung gefeuert
- `MediaReferencesSynced` - Wird nach Synchronisation von Referenzen gefeuert

### 3. **Return Type Declarations**
Alle Methoden haben nun explizite Return Types:
```php
public function uploadFile(...): Media
public function createFromExisting(...): Media
public function getUnusedMedia(): Collection
public function getMedia(...): Collection
```

### 4. **Database Transactions**
Kritische Operationen (Upload, Move) laufen nun in DB-Transaktionen:
- Bei Fehlern wird automatisch gerollt back
- Dateien werden bei Fehlern aufgeräumt
- Verhindert inkonsistente Zustände

### 5. **Fehlerbehandlung & Logging**
- Exceptions statt `null`-Returns
- Strukturiertes Logging bei allen wichtigen Operationen
- Cleanup bei Fehlern (orphaned files werden entfernt)

### 6. **Security & Validierung**
- MIME-Type Whitelist (konfigurierbar über `config/media.php`)
- Dateigrößen-Limit (Standard: 50MB, konfigurierbar)
- Validierung vor Upload

### 7. **Konfiguration**
Neue Config-Datei `config/media.php`:
- `allowed_mime_types` - Anpassbare MIME-Type Whitelist
- `max_file_size` - Maximale Dateigröße
- `default_disk` - Standard Storage Disk
- `default_collection` - Standard Collection

### 8. **Verbesserte Methoden**

#### `uploadFile()`
- ✅ Validierung (MIME-Type, Größe)
- ✅ DB-Transaktion
- ✅ Exception Handling
- ✅ Event Dispatching
- ✅ Logging
- ✅ Cleanup bei Fehler

#### `createFromExisting()`
- ✅ DB-Transaktion
- ✅ Rollback bei Fehler (Datei wird zurückverschoben)
- ✅ Exception statt `null`
- ✅ Event Dispatching
- ✅ Logging

#### `deleteMedia()`
- ✅ Prüfung auf Verwendung
- ✅ Exception wenn in Verwendung
- ✅ Event Dispatching
- ✅ Logging

#### `syncContentMedia()`
- ✅ DB-Transaktion
- ✅ Event Dispatching
- ✅ Logging

#### `createZipArchive()`
- ✅ Besseres Exception Handling
- ✅ Cleanup bei Fehler
- ✅ Validierung (keine Medien gefunden)
- ✅ Logging

## 📋 Verwendung

### Beispiel: Upload mit Fehlerbehandlung
```php
use App\Exceptions\Media\InvalidMediaFileException;
use App\Exceptions\Media\MediaStorageException;

try {
    $media = $mediaService->uploadFile($file, 'images');
} catch (InvalidMediaFileException $e) {
    // Ungültige Datei (MIME-Type oder Größe)
    return back()->withErrors(['file' => $e->getMessage()]);
} catch (MediaStorageException $e) {
    // Storage-Fehler
    Log::error('Upload failed', ['error' => $e->getMessage()]);
    return back()->withErrors(['file' => 'Upload fehlgeschlagen']);
}
```

### Beispiel: Löschen mit Fehlerbehandlung
```php
use App\Exceptions\Media\MediaInUseException;

try {
    $mediaService->deleteMedia($media);
} catch (MediaInUseException $e) {
    // Medium wird noch verwendet
    return back()->withErrors(['media' => $e->getMessage()]);
}
```

### Beispiel: Event Listener registrieren
```php
// In EventServiceProvider
protected $listen = [
    MediaUploaded::class => [
        SendMediaUploadNotification::class,
        OptimizeMediaImage::class,
    ],
    MediaDeleted::class => [
        ClearMediaCache::class,
    ],
];
```

## 🔧 Konfiguration

In `.env`:
```env
MEDIA_MAX_FILE_SIZE=52428800  # 50MB in Bytes
MEDIA_DEFAULT_DISK=public
MEDIA_DEFAULT_COLLECTION=default
```

In `config/media.php`:
```php
'allowed_mime_types' => [
    'image/jpeg',
    'image/png',
    // ... weitere
],
```

## 🎯 Vorteile

1. **Fehlerbehandlung**: Klare, typisierte Exceptions statt unklare `null`-Returns
2. **Transaktionssicherheit**: Keine inkonsistenten Datenbank/Dateisystem-Zustände
3. **Erweiterbarkeit**: Events ermöglichen Hooks für zusätzliche Logik
4. **Sicherheit**: MIME-Type & Größen-Validierung
5. **Debugging**: Strukturiertes Logging für alle Operationen
6. **Konfigurierbarkeit**: Zentrale Config-Datei
7. **Type Safety**: Vollständige Type-Hints und Return Types
8. **Testbarkeit**: Bessere Testbarkeit durch klare Exceptions

## 📝 Hinweise

- **Breaking Changes**: Methoden werfen nun Exceptions statt `null` zurückzugeben
- **Migration**: Bestehender Code muss angepasst werden (Try-Catch-Blöcke)
- **Events**: Optional - können ignoriert werden wenn nicht benötigt
- **Config**: Kann in bestehende Config-Struktur integriert werden
