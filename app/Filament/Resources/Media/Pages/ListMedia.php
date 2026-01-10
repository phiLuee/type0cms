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
                ->schema([
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
                    $mediaService = app(\App\Services\MediaService::class);
                    $uploaded = 0;

                    foreach ($data['files'] as $filePath) {
                        try {
                            $mediaService->createFromExisting(
                                sourcePath: $filePath,
                                sourceDisk: 'public',
                                collection: $data['collection'],
                                originalName: basename($filePath)
                            );
                            $uploaded++;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Media upload failed: ' . $e->getMessage());
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
                    $mediaService = app(\App\Services\MediaService::class);
                    $stats = $mediaService->getStatistics();

                    $model = static::getResource()::getModel();
                    $imageCount = $model::where('mime_type', 'like', 'image/%')->count();
                    $videoCount = $model::where('mime_type', 'like', 'video/%')->count();
                    $documentCount = $model::where('mime_type', 'like', 'application/%')->count();

                    return view('filament.components.media-statistics', [
                        'totalCount' => $stats['total_count'],
                        'totalSize' => $stats['total_size'],
                        'imageCount' => $imageCount,
                        'videoCount' => $videoCount,
                        'documentCount' => $documentCount,
                        'unusedCount' => $stats['unused_count'],
                        'unusedSize' => $stats['unused_size'],
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Schließen'),

            Actions\Action::make('cleanup')
                ->label('Aufräumen')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Wirklich ungenutzte Medien löschen')
                ->modalDescription('Löscht nur Medien, die weder an Models gebunden sind noch in Texten verwendet werden.')
                ->action(function () {
                    $service = app(\App\Services\MediaService::class);
                    $count = $service->deleteUnusedMedia();

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
        $model = static::getResource()::getModel();

        return [
            'all' => Tab::make('Alle')
                ->badge($model::count()),

            'images' => Tab::make('Bilder')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('mime_type', 'like', 'image/%'))
                ->badge($model::where('mime_type', 'like', 'image/%')->count()),

            'videos' => Tab::make('Videos')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('mime_type', 'like', 'video/%'))
                ->badge($model::where('mime_type', 'like', 'video/%')->count()),

            'documents' => Tab::make('Dokumente')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('mime_type', 'like', 'application/%'))
                ->badge($model::where('mime_type', 'like', 'application/%')->count()),

            'unused' => Tab::make('Ungenutzt')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->whereDoesntHave('references')
                )
                ->badge(
                    $model::whereDoesntHave('references')->count()
                )
                ->badgeColor('danger'),
        ];
    }
}
