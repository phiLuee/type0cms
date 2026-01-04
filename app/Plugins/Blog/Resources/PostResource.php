<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Resources;

use App\Plugins\Blog\Models\Post;
use App\Plugins\Blog\Resources\PostResource\Pages;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use BackedEnum;
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
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                                Forms\Components\Select::make('blog_category_id')
                                    ->relationship('category', 'name')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->unique('blog_categories', 'slug'),
                                    ]),
                                Forms\Components\RichEditor::make('content')->columnSpanFull(),
                                Forms\Components\Toggle::make('is_published'),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
