<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('upload')
                ->label('Medien hochladen')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Neue Medien hochladen')
                ->modalDescription('Laden Sie Bilder, Videos, Dokumente oder andere Dateien hoch.')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('files')
                        ->label('Dateien')
                        ->multiple()
                        ->disk('public')
                        ->directory('media')
                        ->visibility('public')
                        ->downloadable()
                        ->previewable()
                        ->acceptedFileTypes([
                            'image/*',
                            'video/*',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(10240) // 10MB
                        ->required(),

                    \Filament\Forms\Components\Select::make('collection')
                        ->label('Sammlung')
                        ->options([
                            'default' => 'Standard',
                            'featured' => 'Titelbilder',
                            'gallery' => 'Galerie',
                            'attachments' => 'Anhänge',
                            'documents' => 'Dokumente',
                        ])
                        ->default('default')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $uploaded = 0;

                    foreach ($data['files'] as $file) {
                        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($file);

                        if (file_exists($path)) {
                            \Spatie\MediaLibrary\MediaCollections\Models\Media::create([
                                'model_type' => null,
                                'model_id' => null,
                                'name' => pathinfo($file, PATHINFO_FILENAME),
                                'file_name' => basename($file),
                                'mime_type' => mime_content_type($path),
                                'disk' => 'public',
                                'collection_name' => $data['collection'],
                                'size' => filesize($path),
                                'manipulations' => [],
                                'custom_properties' => [],
                                'generated_conversions' => [],
                                'responsive_images' => [],
                                'uuid' => \Illuminate\Support\Str::uuid(),
                                'order_column' => 1,
                            ]);

                            $uploaded++;
                        }
                    }

                    Notification::make()
                        ->success()
                        ->title('Upload erfolgreich')
                        ->body("{$uploaded} Datei(en) wurden hochgeladen.")
                        ->send();
                }),

            Actions\Action::make('duplicates')
                ->label('Duplikate finden')
                ->icon('heroicon-o-document-duplicate')
                ->color('warning')
                ->modalHeading('Duplizierte Medien')
                ->modalContent(function () {
                    $service = app(\App\Services\MediaService::class);
                    $duplicates = $service->findDuplicates();

                    return view('filament.components.media-duplicates', compact('duplicates'));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Schließen'),

            Actions\Action::make('report')
                ->label('Bericht')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->modalHeading('Medien-Nutzungsbericht')
                ->modalContent(function () {
                    $service = app(\App\Services\MediaService::class);
                    $report = $service->generateUsageReport();

                    return view('filament.components.media-report', compact('report'));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Schließen'),

            Actions\Action::make('statistics')
                ->label('Statistiken')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('Medien-Statistiken')
                ->modalContent(function () {
                    $model = static::getResource()::getModel();
                    $totalCount = $model::count();
                    $totalSize = $model::sum('size');
                    $imageCount = $model::where('mime_type', 'like', 'image/%')->count();
                    $videoCount = $model::where('mime_type', 'like', 'video/%')->count();
                    $documentCount = $model::where('mime_type', 'like', 'application/%')->count();
                    $unusedCount = $model::whereNull('model_type')->count();
                    $unusedSize = $model::whereNull('model_type')->sum('size');

                    return view('filament.components.media-statistics', compact(
                        'totalCount',
                        'totalSize',
                        'imageCount',
                        'videoCount',
                        'documentCount',
                        'unusedCount',
                        'unusedSize'
                    ));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Schließen'),

            Actions\Action::make('cleanup')
                ->label('Aufräumen')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Ungenutzte Medien löschen')
                ->modalDescription('Möchten Sie wirklich alle ungenutzten Medien löschen?')
                ->action(function () {
                    $count = static::getResource()::getModel()::whereNull('model_type')->count();
                    static::getResource()::getModel()::whereNull('model_type')->each(fn($media) => $media->delete());
                    Notification::make()
                        ->success()
                        ->title('Aufräumen erfolgreich')
                        ->body("{$count} ungenutzte Medien wurden gelöscht.")
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Alle'),
            'images' => Tab::make('Bilder')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('mime_type', 'like', 'image/%'))
                ->badge(static::getResource()::getModel()::where('mime_type', 'like', 'image/%')->count()),
            'videos' => Tab::make('Videos')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('mime_type', 'like', 'video/%'))
                ->badge(static::getResource()::getModel()::where('mime_type', 'like', 'video/%')->count()),
            'documents' => Tab::make('Dokumente')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('mime_type', 'like', 'application/%'))
                ->badge(static::getResource()::getModel()::where('mime_type', 'like', 'application/%')->count()),
            'unused' => Tab::make('Ungenutzt')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('model_type'))
                ->badge(static::getResource()::getModel()::whereNull('model_type')->count())
                ->badgeColor('danger'),
        ];
    }
}
