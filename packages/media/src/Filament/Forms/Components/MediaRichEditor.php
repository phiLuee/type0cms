<?php

declare(strict_types=1);

namespace Type0\Media\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor as BaseRichEditor;
use Type0\Media\Models\Media;

class MediaRichEditor extends BaseRichEditor
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extraInputAttributes([
            'style' => 'min-height: 12rem;',
        ]);

        $this->hintAction(
            Action::make('insertMedia')
                ->label('Medium einfügen')
                ->icon('heroicon-o-photo')
                ->modalHeading('Medium aus Mediathek einfügen')
                ->modalWidth('5xl')
                ->form([
                    \Filament\Forms\Components\Select::make('media_id')
                        ->label('Medium auswählen')
                        ->searchable()
                        ->options(function () {
                            return Media::query()
                                ->orderBy('created_at', 'desc')
                                ->limit(200)
                                ->get()
                                ->mapWithKeys(function (Media $media) {
                                    $icon = match (true) {
                                        str_starts_with($media->mime_type, 'image/') => '🖼️',
                                        str_starts_with($media->mime_type, 'video/') => '🎥',
                                        $media->mime_type === 'application/pdf' => '📄',
                                        default => '📎',
                                    };

                                    $size = round($media->size / 1024, 1) . ' KB';

                                    return [
                                        $media->id => "{$icon} {$media->name} ({$size})"
                                    ];
                                });
                        })
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $media = Media::find($state);
                                if ($media) {
                                    $set('preview_url', $media->getUrl());
                                    $set('preview_type', $media->mime_type);
                                    $set('preview_name', $media->name);
                                }
                            }
                        }),

                    \Filament\Forms\Components\Placeholder::make('preview')
                        ->label('Vorschau')
                        ->content(function ($get) {
                            $mediaId = $get('media_id');
                            if (!$mediaId) {
                                return '';
                            }

                            $media = Media::find($mediaId);
                            if (!$media) {
                                return '';
                            }

                            $url = $media->getUrl();

                            if (str_starts_with($media->mime_type, 'image/')) {
                                return new \Illuminate\Support\HtmlString(
                                    '<img src="' . $url . '" style="max-width: 300px; max-height: 200px; border-radius: 0.5rem;" />'
                                );
                            }

                            $icon = match (true) {
                                $media->mime_type === 'application/pdf' => '📄',
                                str_starts_with($media->mime_type, 'video/') => '🎥',
                                str_starts_with($media->mime_type, 'audio/') => '🎵',
                                default => '📎',
                            };

                            return new \Illuminate\Support\HtmlString(
                                '<div style="padding: 1rem; background: #f3f4f6; border-radius: 0.5rem;">' .
                                    '<div style="font-size: 2rem; margin-bottom: 0.5rem;">' . $icon . '</div>' .
                                    '<div><strong>' . htmlspecialchars($media->name) . '</strong></div>' .
                                    '<div style="color: #6b7280; font-size: 0.875rem;">' . htmlspecialchars($media->mime_type) . '</div>' .
                                    '</div>'
                            );
                        })
                        ->visible(fn($get) => $get('media_id') !== null),

                    \Filament\Forms\Components\TextInput::make('link_text')
                        ->label('Link-Text (optional)')
                        ->hint('Nur für PDFs und andere Dateien. Bei Bildern wird das Bild eingefügt.')
                        ->placeholder('z.B. "PDF herunterladen"')
                        ->visible(function ($get) {
                            if (!$get('media_id')) {
                                return false;
                            }
                            $media = Media::find($get('media_id'));
                            return $media && !str_starts_with($media->mime_type, 'image/');
                        }),
                ])
                ->action(function (array $data, $livewire, $component) {
                    $media = Media::find($data['media_id']);

                    if (!$media) {
                        return;
                    }

                    $url = $media->getUrl();
                    $name = $media->name;
                    $mimeType = $media->mime_type;

                    if (str_starts_with($mimeType, 'image/')) {
                        $html = sprintf(
                            '<img src="%s" alt="%s" title="%s" />',
                            $url,
                            htmlspecialchars($name),
                            htmlspecialchars($name)
                        );
                    } else {
                        $linkText = $data['link_text'] ?? $name;
                        $icon = match (true) {
                            $mimeType === 'application/pdf' => '📄',
                            str_starts_with($mimeType, 'video/') => '🎥',
                            str_starts_with($mimeType, 'audio/') => '🎵',
                            default => '📎',
                        };

                        $html = sprintf(
                            '<a href="%s" target="_blank" rel="noopener">%s %s</a>',
                            $url,
                            $icon,
                            htmlspecialchars($linkText)
                        );
                    }

                    $currentContent = $component->getState() ?? '';
                    $component->state($currentContent . ' ' . $html);
                })
        );
    }
}
