<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Resources;

use App\Plugins\Blog\Models\Post;
use App\Plugins\Blog\Resources\PostResource\Pages;
use Filament\Forms;
use Filament\Schemas\Schema;
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
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('blog_category_id')
                    ->relationship('category', 'name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('slug')->required(),
                    ]),
                Forms\Components\RichEditor::make('content')->columnSpanFull(),
                Forms\Components\Toggle::make('is_published'),
            ]);
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
