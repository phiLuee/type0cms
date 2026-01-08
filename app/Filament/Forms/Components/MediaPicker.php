<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPicker extends Field
{
    protected string $view = 'filament.forms.components.media-picker';

    protected ?string $collection = null;

    protected bool $multiple = false;

    public function collection(string $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function getCollection(): ?string
    {
        return $this->collection;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function getAvailableMedia(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Media::query()->orderBy('created_at', 'desc');

        if ($this->collection) {
            $query->where('collection_name', $this->collection);
        }

        return $query->get();
    }
}
