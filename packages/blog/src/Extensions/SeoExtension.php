<?php

declare(strict_types=1);

namespace Type0\Blog\Extensions;

use Type0\Blog\Contracts\BlogExtension;
use Illuminate\Database\Eloquent\Model;

/**
 * Built-in blog extension that integrates the optional type0/seo package.
 *
 * Activated automatically when the SEO package is installed.
 * This keeps the SEO package completely standalone with zero blog knowledge.
 */
class SeoExtension implements BlogExtension
{
    protected mixed $cachedOgImageUpload = null;

    public function registerRelationships(string $modelClass): void
    {
        $modelClass::resolveRelationUsing('seo', function ($model) {
            return $model->morphOne(\Type0\Seo\Models\SeoMetadata::class, 'model');
        });
    }

    public function formSchema(): array
    {
        return [
            \Type0\Seo\Filament\Forms\SeoFormSchema::make(),
        ];
    }

    public function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['seo'])) {
            unset($data['seo']['og_image']);
        }

        return $data;
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['seo']['og_image'])) {
            $this->cachedOgImageUpload = $data['seo']['og_image'];
            unset($data['seo']['og_image']);
        }

        return $data;
    }

    public function afterSave(Model $record): void
    {
        if (isset($this->cachedOgImageUpload) && method_exists($record, 'handleSeoOgImageUpload')) {
            $record->handleSeoOgImageUpload(
                $this->cachedOgImageUpload,
                class_basename($record) . ' OG Image: ' . ($record->title ?? $record->name ?? $record->id)
            );
        }

        $this->cachedOgImageUpload = null;
    }
}
