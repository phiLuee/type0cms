# PDF im Post-Text verlinken - Anleitung

## Schnellstart

1. **Gehe zu einem Post** (Blog → Posts → Bearbeiten oder Neu erstellen)

2. **Im Content-Feld** siehst du rechts oben neben den Editor-Buttons einen Button **"Medium einfügen"** mit 📷 Icon

3. **Klicke darauf** - es öffnet sich ein Modal mit der Mediathek-Auswahl

4. **Wähle deine PDF aus** der Liste
   - PDFs sind mit 📄 markiert
   - Bilder mit 🖼️
   - Videos mit 🎥
   - Es wird eine Vorschau angezeigt

5. **Bei PDFs:** Gib optional einen eigenen Link-Text ein
   - z.B. "Anleitung herunterladen"
   - z.B. "PDF öffnen"
   - z.B. "Weitere Informationen"
   - Wenn leer, wird der Dateiname verwendet

6. **Klicke auf "Speichern"**
   - Das Medium wird an der Cursor-Position eingefügt
   - PDFs werden als klickbarer Link mit Icon eingefügt: 📄 Dein Text
   - Bilder werden direkt als Bild angezeigt

## Beispiel-Ergebnisse

### PDF-Link
Wenn du eine PDF mit dem Text "Produktkatalog herunterladen" einfügst:
```html
<a href="http://localhost/storage/media/01KEEXP2BEF1G6P7P9YTQVTGQC.pdf" target="_blank" rel="noopener">📄 Produktkatalog herunterladen</a>
```

Im Frontend sieht der Benutzer dann:
> 📄 Produktkatalog herunterladen

### Bild
Wenn du ein Bild einfügst:
```html
<img src="http://localhost/storage/media/01KEEWC8YZCHGBPFF7HKWNG7KF.jpg" alt="Bildname" title="Bildname" />
```

## Vorteile

✅ **Zentrale Mediathek**: PDF wird nur einmal hochgeladen
✅ **Wiederverwendbar**: Dieselbe PDF kann in mehreren Posts verwendet werden
✅ **Automatische URLs**: Die richtigen URLs werden automatisch eingefügt
✅ **Saubere URLs**: Ohne ID im Pfad (z.B. `/storage/media/ULID.pdf`)
✅ **Sicherer Link**: Öffnet in neuem Tab mit `rel="noopener"`

## Medien hochladen

Falls du noch keine Medien hast:

1. Gehe zu **Inhalt → Medien**
2. Klicke auf **"Neues Medium"** oder lade direkt über die Seite hoch
3. Wähle deine PDF-Datei aus
4. Gib einen aussagekräftigen Namen ein (z.B. "Produktkatalog 2026")
5. Wähle die Collection (z.B. "documents")
6. Speichern

Die PDF ist jetzt in allen RichEditors verfügbar!

## Technische Details

- **URL-Format**: `http://localhost/storage/media/{ULID}.pdf`
- **Physischer Pfad**: `storage/app/public/media/{ULID}.pdf`
- **Component**: `App\Filament\Forms\Components\MediaRichEditor`
- **Trait für Referenzen**: `App\Traits\HasMediaReferences`

## Alternative: Programmatische Verwendung

Du kannst Medien auch programmatisch an Posts hängen:

```php
use App\Plugins\Blog\Models\Post;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

$post = Post::find(1);
$media = Media::find(12);

// PDF als Referenz anhängen
$post->attachMedia($media, 'attachments');

// Abrufen
$pdf = $post->getFirstMedia('attachments');
$url = $post->getFirstMediaUrl('attachments');

// Im Template verwenden
<a href="{{ $post->getFirstMediaUrl('attachments') }}">PDF herunterladen</a>
```

## Best Practice

✅ **Empfohlen**: RichEditor für inline PDFs im Fließtext
✅ **Empfohlen**: `attachMedia()` für dedizierte Anhänge/Downloads
❌ **Nicht**: Mehrfach dieselbe PDF hochladen - nutze die Mediathek!
