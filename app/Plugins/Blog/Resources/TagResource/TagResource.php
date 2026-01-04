<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Resources\TagResource;

use App\Plugins\Blog\Models\Tag;
use App\Plugins\Blog\Resources\TagResource\Pages;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Tag')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        ColorPicker::make('color')
                            ->label('Farbe (optional)')
                            ->helperText('Wird für die Darstellung von Tag-Badges verwendet'),
                    ]),
                Section::make('Suchmaschinen Optimierung (SEO)')
                    ->relationship('seo')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Titel')
                            ->placeholder('Wird automatisch vom Tag-Namen übernommen, falls leer')
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

    /**
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                ColorColumn::make('color')
                    ->label('Farbe'),
                TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Anzahl Posts')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    /**
     * @return array<string, string>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'view' => Pages\ViewTag::route('/{record}'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    /**
     * @return string|null
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
