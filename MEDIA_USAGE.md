# Media System - Verwendung

## Upload-Funktion

Über die Medien-Übersicht gibt es jetzt einen **"Medien hochladen"** Button, der ein Modal öffnet:

- ✅ Mehrfach-Upload möglich
- ✅ Sammlung auswählbar
- ✅ Unterstützte Formate: Bilder, Videos, PDFs, Office-Dokumente
- ✅ Max. 10MB pro Datei

## Media RichEditor - Medien in Texte einfügen

### Verwendung des MediaRichEditor:

```php
use App\Filament\Forms\Components\MediaRichEditor;

// In deinem Form Schema:
MediaRichEditor::make('content')
    ->label('Inhalt')
    ->toolbarButtons([
        'bold',
        'italic',
        'link',
        'heading',
        'bulletList',
        'orderedList',
        // Media-Button wird automatisch hinzugefügt
    ])
    ->columnSpanFull(),
```

**Features:**
- ✅ Media-Button in der Toolbar (📷 Icon)
- ✅ Modal mit allen verfügbaren Medien
- ✅ Suchfunktion zum Filtern
- ✅ Bilder werden als `<img>` eingefügt
- ✅ Andere Dateien als Download-Links
- ✅ Visual Grid mit Thumbnails

## Media Picker in Forms

### Verwendung in anderen Resources:

```php
use App\Filament\Forms\Components\MediaPicker;

// In deinem Form Schema:
MediaPicker::make('featured_image_id')
    ->label('Titelbild')
    ->collection('gallery') // Optional: Filter nach Collection
    ->required(),

// Oder für mehrere Medien:
MediaPicker::make('gallery_images')
    ->label('Galerie')
    ->multiple()
    ->collection('gallery'),
```

### Beispiel Blog Post mit Media Picker:

```php
Section::make('Medien')
    ->schema([
        MediaPicker::make('featured_image_id')
            ->label('Titelbild')
            ->collection('featured'),
        
        MediaPicker::make('gallery_images')
            ->label('Galerie')
            ->multiple()
            ->collection('gallery'),
        
        MediaPicker::make('attachment_ids')
            ->label('Anhänge')
            ->multiple()
            ->collection('attachments'),
    ])
    ->collapsed(),
```

## Medien in Blade Templates ausgeben

```blade
{{-- Einzelnes Medium --}}
@if($post->featured_image_id)
    @php
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($post->featured_image_id);
    @endphp
    <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}">
@endif

{{-- Mehrere Medien --}}
@foreach($post->gallery_images ?? [] as $imageId)
    @php
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($imageId);
    @endphp
    <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}">
@endforeach

{{-- Medien aus RichEditor Content (HTML wird bereits gerendert) --}}
{!! $post->content !!}
```

## Alternative: Direkter Spatie Media Library Upload

Wenn du Spatie direkt verwenden möchtest (für Models mit HasMedia Interface):

```php
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

SpatieMediaLibraryFileUpload::make('content_images')
    ->label('Bilder')
    ->collection('content')
    ->multiple()
    ->image()
    ->imageEditor()
    ->responsiveImages(),
```

Dies speichert die Beziehung direkt am Model (benötigt `HasMedia` Interface).

## Workflow für Blog Posts

1. **Medien hochladen:** Über "Medien hochladen" Button in der Media-Übersicht
2. **Im Editor einfügen:** Über den Media-Button (📷) im RichEditor
3. **Titelbild setzen:** Via MediaPicker oder SpatieMediaLibraryFileUpload
4. **SEO-Bild:** Bereits über SpatieMediaLibraryFileUpload in der SEO-Section

Alle Medien werden zentral verwaltet und können überall wiederverwendet werden!

