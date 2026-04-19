<?php

declare(strict_types=1);

namespace Type0\Media\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Type0\Media\Models\Media;

class MediaCollectionsWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $collections = Media::selectRaw('collection_name, COUNT(*) as count')
            ->groupBy('collection_name')
            ->pluck('count', 'collection_name')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Anzahl Medien',
                    'data' => array_values($collections),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(99, 102, 241)',
                        'rgb(107, 114, 128)',
                    ],
                ],
            ],
            'labels' => array_map(fn($name) => match ($name) {
                'default' => 'Standard',
                'featured' => 'Titelbilder',
                'gallery' => 'Galerie',
                'attachments' => 'Anhänge',
                'documents' => 'Dokumente',
                default => ucfirst($name),
            }, array_keys($collections)),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
