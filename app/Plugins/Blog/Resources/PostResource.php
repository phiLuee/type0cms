<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Resources;

use App\Plugins\Blog\Models\Post;
use App\Plugins\Blog\Resources\PostResource\Pages;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;


class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Inhalt')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                                Select::make('blog_category_id')
                                    ->relationship('category', 'name')
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique('blog_categories', 'slug'),
                                    ]),
                                Select::make('tags')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique('blog_tags', 'slug'),
                                    ]),
                                RichEditor::make('content')->columnSpanFull(),
                                Toggle::make('is_published'),
                            ]),
                        Section::make('Suchmaschinen Optimierung (SEO)')
                            ->relationship('seo')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('Meta Titel')
                                    ->placeholder('Wird automatisch vom Beitragstitel übernommen, falls leer')
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
                    ])->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Toggle::make('is_published'),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('category.name'),
                IconColumn::make('is_published')->boolean(),
                TextColumn::make('created_at')->dateTime(),
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

    /**
     * @return array<string, class-string>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
