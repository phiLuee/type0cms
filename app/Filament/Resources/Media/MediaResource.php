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
use App\Models\Media;
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
                Forms\Components\Placeholder::make('usage_warning')
                    ->label('')
                    ->content(function ($record) {
                        if (!$record) {
                            return null;
                        }

                        $referencesCount = $record->references()->count();

                        if ($referencesCount === 0) {
                            return null;
                        }

                        $references = $record->references()->with('model')->get();
                        $models = $references->pluck('model')->filter()->unique('id');
                        $modelNames = $models->map(fn($m) => $m->title ?? $m->name ?? class_basename($m))->join(', ');

                        return "⚠️ **Achtung:** Dieses Medium wird an {$referencesCount} Stelle(n) verwendet: {$modelNames}. Es kann nicht gelöscht werden, solange diese Referenzen bestehen.";
                    })
                    ->visible(fn($record) => $record && $record->references()->count() > 0)
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-warning-600 dark:text-warning-400 bg-warning-50 dark:bg-warning-950 p-4 rounded-lg border border-warning-200 dark:border-warning-800']),

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
                        try {
                            if (str_starts_with($record->mime_type ?? '', 'image/')) {
                                return $record->getUrl();
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error getting media URL: ' . $e->getMessage());
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
                    ->label('Direkt gebunden')
                    ->formatStateUsing(
                        fn($state, Media $record) =>
                        $state ? class_basename($state) : 'Nicht gebunden'
                    )
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('references_count')
                    ->label('Referenzen')
                    ->counts('references')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 0 => 'gray',
                        $state <= 2 => 'warning',
                        default => 'danger',
                    })
                    ->icon(fn($state) => $state > 0 ? 'heroicon-m-link' : null)
                    ->tooltip(function (Media $record) {
                        $count = $record->references()->count();
                        if ($count === 0) {
                            return 'Keine Referenzen - Medium kann gelöscht werden';
                        }

                        $references = $record->references()->with('model')->take(5)->get();
                        $models = $references->pluck('model')->filter()->map(function ($model) {
                            return class_basename($model) . ': ' . ($model->title ?? $model->name ?? 'ID ' . $model->id);
                        })->join("\n");

                        $tooltip = "{$count} " . ($count === 1 ? 'Verwendung' : 'Verwendungen') . ":\n" . $models;

                        if ($count > 5) {
                            $tooltip .= "\n... und " . ($count - 5) . " weitere";
                        }

                        return $tooltip;
                    })
                    ->sortable()
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
                    ->label('Wirklich ungenutzt')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereNull('model_type')
                            ->whereDoesntHave('references')
                    ),

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
                    ->requiresConfirmation()
                    ->before(function (Media $record, DeleteAction $action) {
                        $referencesCount = $record->references()->count();

                        if ($referencesCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Medium wird noch verwendet')
                                ->body("Dieses Medium wird noch an {$referencesCount} Stelle(n) referenziert (z.B. im Text-Editor). Es kann nicht gelöscht werden.")
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),
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
                        ->modalDescription('Achtung: Die Medien werden dauerhaft gelöscht und können nicht wiederhergestellt werden.')
                        ->before(function ($records, DeleteBulkAction $action) {
                            $protectedMedia = [];

                            foreach ($records as $record) {
                                $referencesCount = $record->references()->count();
                                if ($referencesCount > 0) {
                                    $protectedMedia[] = "{$record->name} ({$referencesCount} Referenz(en))";
                                }
                            }

                            if (!empty($protectedMedia)) {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title('Einige Medien werden noch verwendet')
                                    ->body('Folgende Medien können nicht gelöscht werden, da sie noch referenziert werden: ' . implode(', ', $protectedMedia))
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }
                        }),
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
