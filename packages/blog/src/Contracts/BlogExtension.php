<?php

declare(strict_types=1);

namespace Type0\Blog\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Contract for blog extensions.
 *
 * Any package can implement this interface and register itself
 * via the BlogExtensionManager to extend blog functionality.
 */
interface BlogExtension
{
    /**
     * Register dynamic relationships on a blog model class.
     *
     * Called once during boot for each blog model (Post, Category, Tag).
     *
     * @param class-string<Model> $modelClass
     */
    public function registerRelationships(string $modelClass): void;

    /**
     * Return Filament form schema components to inject into resource forms.
     *
     * @return array<\Filament\Schemas\Components\Component>
     */
    public function formSchema(): array;

    /**
     * Mutate form data before filling the edit form.
     */
    public function mutateFormDataBeforeFill(array $data): array;

    /**
     * Mutate form data before saving/creating.
     *
     * Use this to extract data that needs special handling in afterSave().
     */
    public function mutateFormDataBeforeSave(array $data): array;

    /**
     * Called after the record has been saved or created.
     */
    public function afterSave(Model $record): void;
}
