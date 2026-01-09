# Cyberpunk Blog - Volt Structure

## 📁 Neue Dateistruktur

```
resources/views/
├── components/
│   └── layouts/
│       └── app.blade.php          # Haupt-Layout mit allen Cyberpunk-Styles
├── pages/
│   ├── home.blade.php             # Homepage (Volt Page)
│   └── blog/
│       └── show.blade.php         # Blog Post Detail (Volt Page)
└── livewire/
    └── blog-post-list.blade.php   # Blog Post Liste (Livewire Component)
```

## 🎨 Layout Komponente

**File:** `resources/views/components/layouts/app.blade.php`

Das zentrale Layout enthält:
- Alle Cyberpunk 2077 + Nier Automata + Sekiro Styles
- Hexagonal Grid Overlay
- Scanline Animation
- Paper Texture
- Digital Noise Overlay
- Sumi-e Ink Splash Effect
- Alle Google Fonts (Orbitron, Rajdhani, Noto Sans/Serif JP, IBM Plex Mono, Crimson Text)
- Tailwind CSS
- Livewire Scripts

**Verwendung:**
```php
layout('components.layouts.app');
```

## 🏠 Homepage

**File:** `resources/views/pages/home.blade.php`

Volt Page mit:
- `layout('components.layouts.app')` - verwendet das Cyberpunk-Layout
- Eingebundene `blog-post-list` Livewire Komponente
- Responsive Container

**Route:** `/` (home)

## 📝 Blog Post Detail

**File:** `resources/views/pages/blog/show.blade.php`

Volt Page mit:
- Dynamic SEO Meta Tags
- Post-spezifische Styles
- Cyber Header mit Zurück-Button
- Featured Image
- Category & Tags
- Title mit Cyberpunk-Effekten
- Meta Information (Author, Date, Reading Time)
- Article Content (Prose)
- Share Buttons (Twitter, Facebook, LinkedIn)
- Related Posts Section
- Cyber Footer

**Route:** `/blog/{post:slug}` (blog.show)

**Properties:**
- `post` - Injected via mount() mit eager loading von author, category, tags, seo

## 🔧 Blog Post Liste

**File:** `resources/views/livewire/blog-post-list.blade.php`

Livewire Component mit:
- Pagination (2 Posts pro Seite)
- Cyberpunk Cards mit:
  - Corner Brackets (Nier Automata)
  - Hanko Seal mit Kanji 刀 (Sekiro)
  - Terminal Category Badge
  - Gradient Title
  - Meta Info
  - Cyber Button
- Empty State mit Glitch-Text
- Grid Layout (responsive: 1 col mobile, 2 cols tablet, 3 cols desktop)

## 🛣️ Routen

**File:** `routes/web.php`

```php
// Homepage
Volt::route('/', 'pages.home')->name('home');

// Blog Post Detail
Route::get('/blog/{post:slug}', function (Post $post) {
    if (!$post->is_published) {
        abort(404);
    }
    return view('pages.blog.show', compact('post'));
})->name('blog.show');
```

## 🎨 Design Features

### Cyberpunk 2077
- Neon Yellow (#fcee09) als Primärfarbe
- Hexagonal Grid Pattern
- Scanline Glitch Animation
- Hologram Sweep Effects
- Clip-Path Geometrie

### Nier Automata
- Terminal UI mit IBM Plex Mono
- Corner Brackets
- Scanline Overlays
- Monochrome Data Labels
- Minimalistisches Terminal-Design

### Sekiro
- Hanko Seals (Octagonal Stamps)
- Sumi-e Ink Splash Effects
- Paper Texture Overlay
- Japanische Kanji (刀 Katana, 武 Warrior)
- Crimson Red Akzente (#dc143c)
- Vertical Japanese Text

## 🚀 Verwendung

### Neue Volt Page erstellen:
```php
<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.app');
title('Meine Seite');
?>

<div>
    <!-- Content -->
</div>
```

### Livewire Component einbinden:
```blade
<livewire:blog-post-list />
```

### Route hinzufügen:
```php
Volt::route('/my-page', 'pages.my-page')->name('my-page');
```

## 📦 Dependencies

- Laravel 11.x
- Livewire 3.x
- Volt (Livewire Functional Component)
- Tailwind CSS 4.x
- Spatie Media Library (für Featured Images)
