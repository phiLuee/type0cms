<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

/**
 * Wiederverwendbares SEO Form Schema für alle Resources
 */
class SeoFormSchema
{
    /**
     * Gibt das vollständige SEO-Schema für Filament Forms zurück
     * 
     * @param bool $collapsed Ob die Section initial eingeklappt ist
     * @return Section
     */
    public static function make(bool $collapsed = true): Section
    {
        return Section::make('Suchmaschinen Optimierung (SEO)')
            ->relationship('seo')
            ->schema([
                self::metaTitleField(),
                self::metaDescriptionField(),
                self::ogImagePreview(),
                self::ogImageDeleteButton(),
                self::ogImageUpload(),
                self::noIndexToggle(),
            ])
            ->collapsed($collapsed);
    }

    /**
     * Meta Title Feld
     */
    protected static function metaTitleField(): TextInput
    {
        return TextInput::make('meta_title')
            ->label('Meta Titel')
            ->placeholder('Wird automatisch vom Beitragstitel übernommen, falls leer')
            ->maxLength(60)
            ->helperText('Optimal: 50-60 Zeichen');
    }

    /**
     * Meta Description Feld
     */
    protected static function metaDescriptionField(): Textarea
    {
        return Textarea::make('meta_description')
            ->label('Meta Beschreibung')
            ->rows(3)
            ->maxLength(160)
            ->helperText('Optimal: 150-160 Zeichen');
    }

    /**
     * OG Image Vorschau (nur wenn vorhanden)
     */
    protected static function ogImagePreview(): TextEntry
    {
        return TextEntry::make('og_image_preview')
            ->label('Aktuelles OG Image')
            ->state(function ($record) {
                if (!$record) {
                    return null;
                }

                $ogMedia = $record->getFirstMedia('og_image');
                if (!$ogMedia) {
                    return null;
                }

                return '<div style="margin-top: 0.5rem;">
                    <img src="' . e($ogMedia->getUrl()) . '" 
                         style="max-width: 100%; max-height: 200px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" 
                         alt="OG Image Preview" />
                    <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">
                        ' . e($ogMedia->file_name) . ' (' . number_format($ogMedia->size / 1024, 2) . ' KB)
                    </p>
                </div>';
            })
            ->html()
            ->visible(fn($record) => $record && $record->getFirstMedia('og_image'));
    }

    /**
     * OG Image Löschen Button
     */
    protected static function ogImageDeleteButton(): Action
    {
        return Action::make('delete_og_image')
            ->label('Bild löschen')
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalHeading('OG Image löschen?')
            ->modalDescription('Das Bild wird aus der Mediathek entfernt und kann nicht wiederhergestellt werden.')
            ->modalSubmitActionLabel('Ja, löschen')
            ->action(function ($record, $livewire) {
                if (!$record) {
                    return;
                }

                $ogMedia = $record->getFirstMedia('og_image');
                if ($ogMedia) {
                    $record->detachMedia($ogMedia, 'og_image');

                    // Optional: Media komplett löschen wenn nicht mehr verwendet
                    if ($ogMedia->references()->count() === 0) {
                        $ogMedia->deleteWithFile();
                    }
                }

                // Livewire Component neu laden
                $livewire->dispatch('$refresh');
            })
            ->visible(fn($record) => $record && $record->getFirstMedia('og_image'));
    }

    /**
     * OG Image Upload Feld
     */
    protected static function ogImageUpload(): FileUpload
    {
        return FileUpload::make('og_image')
            ->label('OG Image hochladen/ersetzen')
            ->image()
            ->imageEditor()
            ->disk('public')
            ->directory('livewire-tmp')
            ->maxSize(5120)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->hint('Optimal: 1200×630px (1.91:1 Ratio)')
            ->helperText('Wird automatisch in die Mediathek übernommen')
            ->dehydrated(true);
    }

    /**
     * No-Index Toggle
     */
    protected static function noIndexToggle(): Toggle
    {
        return Toggle::make('no_index')
            ->label('Nicht in Suchmaschinen anzeigen (noindex)')
            ->helperText('Verhindert die Indexierung durch Suchmaschinen');
    }

    /**
     * Kompakte Variante ohne Bilder
     */
    public static function makeCompact(): Section
    {
        return Section::make('SEO')
            ->relationship('seo')
            ->schema([
                self::metaTitleField(),
                self::metaDescriptionField(),
                self::noIndexToggle(),
            ])
            ->columns(1)
            ->collapsed();
    }
}
