<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\RichEditor as BaseRichEditor;
use Illuminate\Support\Facades\Blade;

class MediaRichEditor extends BaseRichEditor
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extraInputAttributes([
            'style' => 'min-height: 12rem;',
            'data-editor-id' => uniqid('editor-'),
        ]);

        // Füge Media-Button zur Toolbar hinzu
        $this->extraAlpineAttributes([
            'x-on:insert-media.window' => '
                if ($event.detail.editorId === $el.dataset.editorId) {
                    const media = $event.detail.media;
                    
                    if (media.mime_type.startsWith("image/")) {
                        // Füge Bild ein
                        const img = `<img src="${media.url}" alt="${media.name}" title="${media.name}" />`;
                        const selection = window.getSelection();
                        const range = selection.getRangeAt(0);
                        const node = document.createElement("div");
                        node.innerHTML = img;
                        range.insertNode(node.firstChild);
                    } else {
                        // Füge Download-Link ein
                        const link = `<a href="${media.url}" target="_blank">${media.name}</a>`;
                        const selection = window.getSelection();
                        const range = selection.getRangeAt(0);
                        const node = document.createElement("div");
                        node.innerHTML = link;
                        range.insertNode(node.firstChild);
                    }
                    
                    // Trigger change event
                    $el.dispatchEvent(new Event("input", { bubbles: true }));
                }
            ',
        ]);
    }

    /**
     * Render den Media-Button
     */
    protected function getMediaButton(): string
    {
        return Blade::render(
            '<x-filament::forms.components.rich-editor-media-button />'
        );
    }
}
