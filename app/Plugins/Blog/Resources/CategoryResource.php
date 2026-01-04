<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Resources;

use App\Plugins\Blog\Models\Category;
use App\Plugins\Blog\Resources\CategoryResource\Pages;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Kategorie')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true),
                    ]),
                Section::make('Suchmaschinen Optimierung (SEO)')
                    ->relationship('seo')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Titel')
                            ->placeholder('Wird automatisch vom Kategorienamen übernommen, falls leer')
                            ->maxLength(60),
                        Textarea::make('meta_description')
                            ->label('Meta Beschreibung')
                            ->rows(3)
                            ->maxLength(160),
                        FileUpload::make('og_image')
                            ->label('Social Media Bild (OG Image)')
                            ->image(),
                        Toggle::make('no_index')
                            ->label('Nicht in Suchmaschinen anzeigen (noindex)'),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('posts_count')->counts('posts'),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
