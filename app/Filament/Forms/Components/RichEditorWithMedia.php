<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\RichEditor as BaseRichEditor;

class RichEditorWithMedia extends BaseRichEditor
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->toolbarButtons([
            'bold',
            'italic',
            'underline',
            'strike',
            'link',
            'heading',
            'bulletList',
            'orderedList',
            'blockquote',
            'codeBlock',
            'undo',
            'redo',
            'media', // Unser custom button
        ]);

        $this->extraInputAttributes([
            'style' => 'min-height: 12rem;',
        ]);
    }

    public function getExtraAlpineAttributes(): array
    {
        return array_merge(parent::getExtraAlpineAttributes(), [
            'x-on:insert-media.window' => '
                if ($event.detail.editorId === $el.id) {
                    const media = $event.detail.media;
                    
                    if (media.mime_type.startsWith("image/")) {
                        editor.chain().focus().setImage({ 
                            src: media.url,
                            alt: media.name,
                            title: media.name 
                        }).run();
                    } else {
                        editor.chain().focus().insertContent(
                            `<a href="${media.url}" target="_blank">${media.name}</a>`
                        ).run();
                    }
                }
            ',
        ]);
    }
}
