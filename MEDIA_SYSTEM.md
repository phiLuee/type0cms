# Media-System Dokumentation

## Übersicht

Das Media-System basiert auf zwei Ansätzen:

### 1. Zentrale Mediathek (Empfohlen für Wiederverwendung)

**Verwendung:** Wenn dieselbe Datei an mehrere Models gehängt werden soll.

**Vorteile:**
- Dateien werden nur einmal hochgeladen und gespeichert
- Eine Datei kann beliebig oft verwendet werden
- Zentrale Verwaltung über die Medien-Ressource in Filament
- Keine Duplikate

**Pfade:**
- Physisch: `storage/app/public/media/{ULID}.pdf`
- URL: `http://localhost/storage/media/{ULID}.pdf`

#### Verwendung in Models

```php
use App\Traits\HasMediaReferences;

class Post extends Model
{
    use HasMediaReferences;
}
```

#### Medien anhängen

```php
// Über die Filament Media-Ressource hochladen
$media = Media::find(1);

// An einen Post anhängen
$post->attachMedia($media, 'featured');

// Oder direkt per ID
$post->attachMedia(1, 'featured');

// Mehrere Medien anhängen
$post->attachMedia(2, 'gallery');
$post->attachMedia(3, 'gallery');
```

#### Medien abrufen

```php
// Erstes Medium einer Collection
$featuredImage = $post->getFirstMedia('featured');

// URL des ersten Mediums
$url = $post->getFirstMediaUrl('featured');

// Alle Medien einer Collection
$galleryImages = $post->getMedia('gallery');

// Prüfen, ob Medien vorhanden sind
if ($post->hasMedia('featured')) {
    // ...
}
```

#### Medien synchronisieren

```php
// Alle Medien einer Collection durch neue ersetzen
$post->syncMedia([1, 2, 3], 'gallery');
```

### 2. Spatie MediaLibrary direkt (Nur für einmalige Verwendung)

**Verwendung:** Wenn eine Datei nur an ein einziges Model gehängt wird.

**Nachteile:**
- Jeder Upload erstellt eine neue Media-Instanz
- Dieselbe Datei muss mehrfach hochgeladen werden, wenn sie an mehrere Models gehängt wird
- Mehr Speicherplatz benötigt

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;
}

// Datei hochladen
$post->addMedia($request->file('image'))
    ->toMediaCollection('featured');

// Abrufen
$url = $post->getFirstMediaUrl('featured');
```

## Best Practices

### ✅ Empfohlen: Zentrale Mediathek

```php
// 1. Datei über Filament Media-Ressource hochladen
// 2. Media-ID notieren (z.B. 42)

// 3. In mehreren Posts verwenden
$post1->attachMedia(42, 'featured');
$post2->attachMedia(42, 'featured');
$post3->attachMedia(42, 'thumbnail');

// Die gleiche Datei wird nur einmal gespeichert!
```

### ❌ Nicht empfohlen: Mehrfache Uploads

```php
// NICHT SO - Verschwendet Speicherplatz!
$post1->addMedia($file)->toMediaCollection('featured');
$post2->addMedia($sameFile)->toMediaCollection('featured');
// Jetzt existiert die Datei 2x im Speicher
```

## Integration in Filament

### Für die zentrale Mediathek (HasMediaReferences)

```php
use Filament\Forms\Components\Select;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

Select::make('featured_media_id')
    ->label('Titelbild')
    ->options(
        Media::where('mime_type', 'like', 'image/%')
            ->pluck('name', 'id')
    )
    ->searchable()
    ->reactive()
    ->afterStateUpdated(function ($state, $record) {
        if ($record && $state) {
            // Alte entfernen und neue anhängen
            $record->mediaReferences()
                ->where('collection_name', 'featured')
                ->delete();
            $record->attachMedia($state, 'featured');
        }
    }),

// Vorschau
ImageColumn::make('featured_image')
    ->getStateUsing(fn($record) => $record->getFirstMediaUrl('featured'))
    ->label('Titelbild'),
```

### Für direkte Spatie MediaLibrary

```php
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

SpatieMediaLibraryFileUpload::make('featured')
    ->collection('featured')
    ->image(),
```

## Migration von direkter zu zentraler Mediathek

Falls du bereits Spatie MediaLibrary direkt verwendest:

```php
// Bestehende Medien in Referenzen umwandeln
$posts = Post::with('media')->get();

foreach ($posts as $post) {
    foreach ($post->getMedia() as $media) {
        // Media-Referenz erstellen
        $post->attachMedia(
            $media->id,
            $media->collection_name,
            $media->order_column
        );
        
        // Optional: model_type und model_id auf null setzen,
        // damit das Medium in der zentralen Bibliothek verfügbar ist
        $media->update([
            'model_type' => null,
            'model_id' => null,
        ]);
    }
}
```

## Zusammenfassung

**Für PDFs, die an mehrere Posts gehängt werden:**
- ✅ Nutze `HasMediaReferences` Trait
- ✅ Upload über Filament Media-Ressource
- ✅ `attachMedia()` zum Anhängen
- URL: `http://localhost/storage/media/{ULID}.pdf`

**Für Dateien, die nur zu einem Model gehören:**
- ⚠️ Nutze Spatie MediaLibrary direkt
- Aber bedenke: Wiederverwendung nicht möglich
