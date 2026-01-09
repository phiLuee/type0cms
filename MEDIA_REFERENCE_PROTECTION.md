# Media Reference Protection System

## Problem

Wenn ein Medium (z.B. ein Bild) auf mehrere Arten verwendet wird:
1. **Direkte Bindung** via Spatie Media Library (z.B. als OG-Image in SEO)
2. **Referenz** via `MediaReference` (z.B. verlinkt im RichEditor)

...und dann die direkte Bindung entfernt wird (z.B. OG-Image gelöscht), löscht Spatie standardmäßig das komplette Medium aus der Datenbank und vom Dateisystem, **obwohl es noch im Text verwendet wird**.

## Lösung

Das System schützt Medien vor versehentlichem Löschen durch drei Mechanismen:

### 1. MediaObserver (`app/Observers/MediaObserver.php`)

```php
public function deleting(Media $media): bool
{
    $referencesCount = $media->references()->count();
    
    if ($referencesCount > 0) {
        // Verhindere das Löschen
        return false;
    }
    
    return true; // Erlaube Löschen wenn keine Referenzen
}
```

**Was passiert:**
- Wenn jemand versucht, ein Medium zu löschen
- Prüft der Observer, ob noch `MediaReference` Einträge existieren
- Falls ja: Löschen wird verhindert (return false)
- Falls nein: Löschen wird erlaubt

### 2. Filament UI Warnings

In `MediaResource.php` wurden die Delete-Actions erweitert:

```php
DeleteAction::make()
    ->before(function (Media $record, DeleteAction $action) {
        if ($record->references()->count() > 0) {
            Notification::make()
                ->warning()
                ->title('Medium wird noch verwendet')
                ->send();
            
            $action->cancel(); // Verhindere Löschen
        }
    })
```

**Was passiert:**
- Benutzer sieht eine Warnung, wenn er ein referenziertes Medium löschen will
- Die Lösch-Aktion wird abgebrochen
- Gilt für einzelne und Bulk-Löschungen

### 3. PostObserver erstellt automatisch Referenzen

Der `PostObserver` scannt den Content nach Media-URLs:

```php
public function saved(Post $post): void
{
    // Finde alle Media-URLs im Content
    preg_match_all('/\/storage\/media\/([^"\'>\s]+)/', $post->content, $matches);
    
    foreach ($matches[1] as $filename) {
        $media = Media::where('file_name', $filename)->first();
        
        if ($media) {
            MediaReference::create([
                'media_id' => $media->id,
                'model_type' => Post::class,
                'model_id' => $post->id,
                'collection_name' => 'content',
            ]);
        }
    }
}
```

## Workflow-Beispiel

### Szenario: OG-Image wird auch im Text verwendet

1. **Upload OG-Image für SEO**
   ```
   → Spatie bindet Medium an SeoMetadata
   → media.model_type = 'App\Models\SeoMetadata'
   → media.model_id = 123
   ```

2. **Bild im RichEditor einfügen**
   ```
   → PostObserver erkennt die URL im content
   → Erstellt MediaReference:
     - media_id: 456
     - model_type: 'App\Plugins\Blog\Models\Post'
     - model_id: 789
     - collection_name: 'content'
   ```

3. **OG-Image aus SEO entfernen**
   ```
   → Spatie versucht Medium zu löschen
   → MediaObserver prüft: references()->count() = 1
   → Löschen wird VERHINDERT
   → Medium bleibt erhalten für Text-Verwendung ✅
   ```

4. **Post später löschen**
   ```
   → PostObserver entfernt MediaReference
   → Jetzt: references()->count() = 0
   → Medium kann sicher gelöscht werden
   ```

## Manuelle Bereinigung

Medien ohne Model-Bindung aber ohne Referenzen können manuell bereinigt werden:

```php
// Zeige wirklich ungenutzte Medien
Media::whereNull('model_type')
    ->whereDoesntHave('references')
    ->get();

// In Filament Admin: Tab "Wirklich ungenutzt"
```

## Logging

Der Observer loggt alle Aktionen:

```
[INFO] Media #123 (image.jpg) wird gelöscht: Keine Referenzen vorhanden.
[WARNING] Versuch Media #456 (logo.png) zu löschen verhindert: 3 Referenz(en) existieren noch.
```

Logs findest du in: `storage/logs/laravel.log`

## Datenbank-Struktur

### media Tabelle (Spatie)
```sql
id, model_type, model_id, file_name, collection_name, ...
```

### media_references Tabelle (Custom)
```sql
id, media_id, model_type, model_id, collection_name, order_column
```

### Beziehungen
- Ein `Media` kann viele `MediaReference` haben (1:n)
- Ein Model (z.B. Post) kann viele `MediaReference` haben (1:n)
- Ein `Media` kann direkt an ein Model gebunden sein (model_type/model_id)

## Wichtig

- **Niemals** `MediaReference` Einträge manuell löschen, ohne zu prüfen ob das Medium noch gebraucht wird
- **Immer** den `PostObserver` aktiv lassen (registriert in `AppServiceProvider`)
- **Medien ohne Referenzen** können über den "Aufräumen"-Button im Admin gelöscht werden
