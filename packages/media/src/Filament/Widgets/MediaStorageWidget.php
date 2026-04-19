<?php

declare(strict_types=1);

namespace Type0\Media\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Type0\Media\Models\Media;

class MediaStorageWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalCount = Media::count();
        $totalSize = (int) Media::sum('size');
        $imageCount = Media::where('mime_type', 'like', 'image/%')->count();
        $videoCount = Media::where('mime_type', 'like', 'video/%')->count();
        $unusedCount = Media::whereDoesntHave('references')->count();
        $unusedSize = (int) Media::whereDoesntHave('references')->sum('size');

        return [
            Stat::make('Gesamt-Medien', $totalCount)
                ->description('Dateien im System')
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Speicherplatz', $this->formatBytes($totalSize))
                ->description('Gesamtgröße aller Medien')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('success'),

            Stat::make('Bilder', $imageCount)
                ->description(number_format($imageCount / max($totalCount, 1) * 100, 1) . '% aller Medien')
                ->descriptionIcon('heroicon-m-photo')
                ->color('info'),

            Stat::make('Videos', $videoCount)
                ->description(number_format($videoCount / max($totalCount, 1) * 100, 1) . '% aller Medien')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('warning'),

            Stat::make('Ungenutzt', $unusedCount)
                ->description($this->formatBytes($unusedSize) . ' Speicher freigeben')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger')
                ->url(route('filament.admin.resources.media.index', ['tableFilters' => ['unused' => true]])),

            Stat::make('Effizienz', number_format((1 - $unusedCount / max($totalCount, 1)) * 100, 1) . '%')
                ->description('Genutzte Medien')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($unusedCount > $totalCount * 0.2 ? 'danger' : 'success'),
        ];
    }

    protected function formatBytes(int|string $bytes, int $precision = 2): string
    {
        $bytes = (int) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
