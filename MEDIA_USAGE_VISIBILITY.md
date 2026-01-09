# Media Usage Visibility in Filament

## Übersicht

Das System zeigt jetzt an **allen relevanten Stellen** in Filament, wo Medien verwendet werden.

## 1. Media-Liste (Table)

### Neue "Referenzen"-Spalte

```
┌────────────────┬──────────────┬─────────────┐
│ Dateiname      │ Direkt       │ Referenzen  │
│                │ gebunden     │             │
├────────────────┼──────────────┼─────────────┤
│ logo.png       │ SeoMetadata  │   🔗 3      │  ← Orange: 1-2 Refs
│ hero.jpg       │ Post         │   🔗 5      │  ← Rot: 3+ Refs  
│ unused.pdf     │ Nicht        │   ⚫ 0      │  ← Grau: 0 Refs
│                │ gebunden     │             │
└────────────────┴──────────────┴─────────────┘
```

**Features:**
- **Badge mit Zahl** - Anzahl der Referenzen
- **Farbcodierung:**
  - Grau (0) = Kann gelöscht werden
  - Orange (1-2) = Wenige Verwendungen
  - Rot (3+) = Viele Verwendungen
- **Link-Icon** wenn Referenzen existieren
- **Hover-Tooltip** zeigt Details:
  ```
  3 Verwendungen:
  Post: Mein Blogartikel
  Post: Zweiter Artikel
  Category: Technologie
  ```

## 2. Media-Ansicht (View)

### Neue "Verwendung"-Sektion

Wenn du ein Medium öffnest (`/admin/media/{id}`), siehst du:

```markdown
┌─ Verwendung ────────────────────────────────────┐
│                                                  │
│ ⚠️ Dieses Medium wird an 3 Stelle(n) verwendet: │
│                                                  │
│ **Post:**                                        │
│ - Cyberpunk Blog Setup (Collection: content)    │
│ - Laravel Tutorial (Collection: content)         │
│                                                  │
│ **Category:**                                    │
│ - Technologie (Collection: featured)             │
│                                                  │
│ *Dieses Medium kann nicht gelöscht werden,*      │
│ *solange diese Referenzen existieren.*           │
│                                                  │
└──────────────────────────────────────────────────┘
```

**Features:**
- **Gruppiert nach Model-Typ** (Post, Category, etc.)
- **Zeigt Namen/Titel** der referenzierenden Models
- **Collection-Name** für jeden Eintrag
- **Automatisch collapsed** wenn keine Referenzen
- **Markdown-Formatierung** für bessere Lesbarkeit

## 3. Media-Bearbeitung (Edit)

### Warnung im Formular

Wenn das Medium referenziert wird, erscheint **oben im Formular**:

```
┌──────────────────────────────────────────────────┐
│ ⚠️ Achtung: Dieses Medium wird an 3 Stelle(n)   │
│ verwendet: Mein Blogartikel, Laravel Tutorial,   │
│ Technologie. Es kann nicht gelöscht werden,      │
│ solange diese Referenzen bestehen.               │
└──────────────────────────────────────────────────┘
```

**Features:**
- **Orange/gelber Hintergrund** für Sichtbarkeit
- **Kurze Liste** der verwendenden Models
- **Nur sichtbar** wenn Referenzen existieren

## 4. Lösch-Aktionen

### Einzelnes Medium löschen

```
Klick auf "Löschen" →

┌─ Warnung ────────────────────────────────────────┐
│ ⚠️ Medium wird noch verwendet                    │
│                                                  │
│ Dieses Medium wird noch an 3 Stelle(n)          │
│ referenziert (z.B. im Text-Editor). Es kann     │
│ nicht gelöscht werden.                           │
│                                                  │
│         [Verstanden]                             │
└──────────────────────────────────────────────────┘

→ Lösch-Aktion wird ABGEBROCHEN
```

### Mehrere Medien löschen (Bulk)

```
Medien auswählen → "Löschen" →

┌─ Warnung ────────────────────────────────────────┐
│ ⚠️ Einige Medien werden noch verwendet           │
│                                                  │
│ Folgende Medien können nicht gelöscht werden:   │
│                                                  │
│ - Logo (3 Referenz(en))                         │
│ - Hero Image (5 Referenz(en))                   │
│                                                  │
│         [Verstanden]                             │
└──────────────────────────────────────────────────┘

→ KEINE Medien werden gelöscht
```

## Technische Details

### Database Queries

Die Implementierung nutzt efficient eager loading:

```php
// In der Tabelle
->counts('references')  // Single COUNT query

// In der Ansicht  
->with('model')  // Eager load die referenzierenden Models
```

### Performance

- **Table:** 1 zusätzliche COUNT-Query pro Page
- **View:** 1 Query mit JOIN für alle Referenzen
- **Tooltip:** Cached bis zu 5 Referenzen

### Model-Beziehungen

```php
// Media Model
public function references(): HasMany
{
    return $this->hasMany(MediaReference::class, 'media_id');
}

// MediaReference Model
public function model()
{
    return $this->morphTo();
}
```

## Workflow-Beispiel

### Szenario: Bild in mehreren Posts verwendet

1. **Upload:** `hero.jpg` hochgeladen
2. **Verwendung:**
   - In Post 1 im Content eingefügt
   - In Post 2 im Content eingefügt
   - Als Featured Image für Category verwendet

3. **Admin öffnet Media-Liste:**
   ```
   hero.jpg | Post | 🔗 3
                    ↑
                    Tooltip zeigt:
                    - Post: Artikel 1
                    - Post: Artikel 2
                    - Category: Tech
   ```

4. **Admin klickt auf Medium:**
   ```
   Verwendung-Sektion zeigt:
   
   Post:
   - Artikel 1 (content)
   - Artikel 2 (content)
   
   Category:
   - Tech (featured)
   ```

5. **Admin versucht zu löschen:**
   ```
   ❌ Warnung: Wird noch verwendet
   → Löschen verhindert
   ```

6. **Admin entfernt Referenzen:**
   - Post 1 wird gelöscht → 1 Referenz weg
   - Post 2 Content bearbeitet → 1 Referenz weg
   - Category Featured-Image entfernt → 1 Referenz weg

7. **Jetzt löschen:**
   ```
   ✅ Keine Referenzen mehr
   → Löschen erlaubt
   ```

## Filter & Suche

In der Media-Liste kannst du filtern:

```php
// Nach Referenz-Status
Tab "Wirklich ungenutzt" 
→ Zeigt nur Medien mit 0 Referenzen UND ohne Model-Bindung

// Zukünftige Erweiterung
Filter "Nur referenzierte Medien"
→ whereHas('references')
```

## Best Practices

1. **Vor dem Löschen:** Immer "Referenzen"-Spalte prüfen
2. **Bei vielen Refs:** Medium-Ansicht öffnen für Details
3. **Cleanup:** "Wirklich ungenutzt"-Tab nutzen für sichere Bereinigung
4. **Monitoring:** Hover über Badge für schnelle Info

## Code-Referenzen

- Table-Spalte: [MediaResource.php#L145-165](app/Filament/Resources/Media/MediaResource.php)
- View-Sektion: [ViewMedia.php#L75-115](app/Filament/Resources/Media/Pages/ViewMedia.php)
- Edit-Warning: [MediaResource.php#L44-62](app/Filament/Resources/Media/MediaResource.php)
- Delete-Protection: [MediaResource.php#L245-265](app/Filament/Resources/Media/MediaResource.php)
