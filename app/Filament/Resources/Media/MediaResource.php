<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Services\MediaService;
use BackedEnum;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Medien';

    protected static ?string $modelLabel = 'Medium';

    protected static ?string $pluralModelLabel = 'Medien';

    protected static string|UnitEnum|null $navigationGroup = 'Inhalt';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Dateiinformationen')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('file_name')
                            ->label('Dateiname')
                            ->disabled(),

                        Forms\Components\Select::make('collection_name')
                            ->label('Sammlung')
                            ->options([
                                'default' => 'Standard',
                                'featured' => 'Titelbilder',
                                'gallery' => 'Galerie',
                                'attachments' => 'Anhänge',
                                'documents' => 'Dokumente',
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('mime_type')
                            ->label('MIME-Type')
                            ->disabled(),

                        Forms\Components\TextInput::make('size')
                            ->label('Dateigröße')
                            ->disabled()
                            ->formatStateUsing(fn($state) => self::formatBytes((int)$state)),

                        Forms\Components\Textarea::make('custom_properties')
                            ->label('Eigenschaften')
                            ->formatStateUsing(fn($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->disabled()
                            ->rows(5),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('Vorschau')
                    ->getStateUsing(function (Media $record) {
                        if (str_starts_with($record->mime_type, 'image/')) {
                            return $record->getUrl();
                        }
                        return null;
                    })
                    ->defaultImageUrl(url('/images/file-placeholder.svg'))
                    ->square()
                    ->imageSize(60),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn(Media $record) => $record->name),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Dateiname')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('collection_name')
                    ->label('Sammlung')
                    ->badge()
                    ->colors([
                        'primary' => 'featured',
                        'success' => 'gallery',
                        'warning' => 'attachments',
                        'info' => 'documents',
                        'gray' => 'default',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn($state) => self::getFileTypeLabel($state))
                    ->color(fn($state) => self::getFileTypeColor($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('size')
                    ->label('Größe')
                    ->formatStateUsing(fn($state) => self::formatBytes($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('model_type')
                    ->label('Verwendet von')
                    ->formatStateUsing(
                        fn($state, Media $record) =>
                        $state ? class_basename($state) : 'Nicht zugeordnet'
                    )
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Hochgeladen')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collection_name')
                    ->label('Sammlung')
                    ->options([
                        'default' => 'Standard',
                        'featured' => 'Titelbilder',
                        'gallery' => 'Galerie',
                        'attachments' => 'Anhänge',
                        'documents' => 'Dokumente',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('mime_type')
                    ->label('Dateityp')
                    ->options([
                        'image' => 'Bilder',
                        'video' => 'Videos',
                        'pdf' => 'PDF',
                        'document' => 'Dokumente',
                        'audio' => 'Audio',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'image' => $query->where('mime_type', 'like', 'image/%'),
                            'video' => $query->where('mime_type', 'like', 'video/%'),
                            'pdf' => $query->where('mime_type', 'application/pdf'),
                            'document' => $query->whereIn('mime_type', [
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ]),
                            'audio' => $query->where('mime_type', 'like', 'audio/%'),
                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('unused')
                    ->label('Ungenutzte Medien')
                    ->query(fn(Builder $query) => $query->whereNull('model_type')),

                Tables\Filters\Filter::make('created_at')
                    ->label('Zeitraum')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Von'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Bis'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(Media $record) => $record->getUrl())
                    ->openUrlInNewTab(),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('changeCollection')
                        ->label('Sammlung ändern')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Forms\Components\Select::make('collection_name')
                                ->label('Neue Sammlung')
                                ->options([
                                    'default' => 'Standard',
                                    'featured' => 'Titelbilder',
                                    'gallery' => 'Galerie',
                                    'attachments' => 'Anhänge',
                                    'documents' => 'Dokumente',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                $record->collection_name = $data['collection_name'];
                                $record->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('download')
                        ->label('Als ZIP herunterladen')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->requiresConfirmation()
                        ->modalHeading('Medien herunterladen')
                        ->modalDescription('Möchten Sie die ausgewählten Medien als ZIP-Archiv herunterladen?')
                        ->action(function ($records) {
                            $service = app(MediaService::class);
                            $zipPath = $service->createZipArchive(
                                $records->pluck('id')->toArray(),
                                'media-export-' . now()->format('Y-m-d-H-i-s') . '.zip'
                            );

                            return response()->download($zipPath)->deleteFileAfterSend(true);
                        }),

                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Achtung: Die Medien werden dauerhaft gelöscht und können nicht wiederhergestellt werden.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-Refresh alle 30 Sekunden
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'view' => Pages\ViewMedia::route('/{record}'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    protected static function getFileTypeLabel(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'Bild',
            str_starts_with($mimeType, 'video/') => 'Video',
            str_starts_with($mimeType, 'audio/') => 'Audio',
            $mimeType === 'application/pdf' => 'PDF',
            str_contains($mimeType, 'word') => 'Word',
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') => 'Excel',
            str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation') => 'PowerPoint',
            default => 'Datei',
        };
    }

    protected static function getFileTypeColor(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'success',
            str_starts_with($mimeType, 'video/') => 'warning',
            str_starts_with($mimeType, 'audio/') => 'info',
            $mimeType === 'application/pdf' => 'danger',
            default => 'gray',
        };
    }
}
