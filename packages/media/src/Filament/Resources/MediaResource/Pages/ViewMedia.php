<?php

namespace Type0\Media\Filament\Resources\MediaResource\Pages;

use Type0\Media\Filament\Resources\MediaResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Pages\ViewRecord;

class ViewMedia extends ViewRecord
{
    protected static string $resource = MediaResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Dateiinformationen')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('file_name')
                            ->label('Dateiname'),

                        TextEntry::make('collection_name')
                            ->label('Sammlung')
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'featured' => 'primary',
                                'gallery' => 'success',
                                'attachments' => 'warning',
                                'documents' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn($state) => match ($state) {
                                'default' => 'Standard',
                                'featured' => 'Titelbilder',
                                'gallery' => 'Galerie',
                                'attachments' => 'Anhänge',
                                'documents' => 'Dokumente',
                                default => ucfirst($state),
                            }),

                        TextEntry::make('mime_type')
                            ->label('MIME-Type'),

                        TextEntry::make('size')
                            ->label('Dateigröße')
                            ->formatStateUsing(fn($state) => MediaResource::formatBytes((int)$state)),

                        TextEntry::make('disk')
                            ->label('Speicher-Disk')
                            ->badge(),

                        TextEntry::make('created_at')
                            ->label('Hochgeladen am')
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Aktualisiert am')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Vorschau')
                    ->schema([
                        ViewEntry::make('preview')
                            ->label('')
                            ->view('media::filament.infolists.components.media-preview'),
                    ])
                    ->collapsible(),

                Section::make('Verwendung')
                    ->schema([
                        ViewEntry::make('usage')
                            ->view('media::filament.infolists.components.media-usage'),
                    ])
                    ->collapsible()
                    ->collapsed(function ($record) {
                        return $record->references()->count() === 0;
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
