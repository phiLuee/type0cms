<?php

declare(strict_types=1);

namespace Type0\Blog;

use Type0\Blog\Contracts\BlogExtension;
use Illuminate\Database\Eloquent\Model;

class BlogExtensionManager
{
    /** @var array<BlogExtension> */
    protected array $extensions = [];

    public function register(BlogExtension $extension): void
    {
        $this->extensions[] = $extension;
    }

    /** @return array<BlogExtension> */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    public function registerRelationships(string $modelClass): void
    {
        foreach ($this->extensions as $extension) {
            $extension->registerRelationships($modelClass);
        }
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    public function formSchema(): array
    {
        $schema = [];

        foreach ($this->extensions as $extension) {
            $schema = array_merge($schema, $extension->formSchema());
        }

        return $schema;
    }

    public function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->extensions as $extension) {
            $data = $extension->mutateFormDataBeforeFill($data);
        }

        return $data;
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        foreach ($this->extensions as $extension) {
            $data = $extension->mutateFormDataBeforeSave($data);
        }

        return $data;
    }

    public function afterSave(Model $record): void
    {
        foreach ($this->extensions as $extension) {
            $extension->afterSave($record);
        }
    }
}
