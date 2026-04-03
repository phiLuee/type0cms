<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Resources;

use App\Filament\Clusters\Blog;
use App\Filament\Forms\SeoFormSchema;
use App\Plugins\Blog\Models\Post;
use App\Plugins\Blog\Resources\PostResource\Pages;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use App\Filament\Forms\Components\MediaRichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
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

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $modelLabel = 'Beitrag';

    protected static ?string $pluralModelLabel = 'Beiträge';

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
                                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
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
                                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique('blog_tags', 'slug'),
                                    ]),
                                TextEntry::make('featured_image_preview')
                                    ->label('Aktuelles Featured Image')
                                    ->state(function ($record) {
                                        if (!$record) {
                                            return null;
                                        }

                                        $media = $record->getFirstMedia('featured_image');
                                        if (!$media) {
                                            return null;
                                        }

                                        return '<div style="margin-top: 0.5rem;">
                                            <img src="' . e($media->getUrl()) . '" 
                                                 style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" 
                                                 alt="Featured Image" />
                                            <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">
                                                ' . e($media->file_name) . ' (' . number_format($media->size / 1024, 2) . ' KB)
                                            </p>
                                        </div>';
                                    })
                                    ->html()
                                    ->visible(fn($record) => $record && $record->getFirstMedia('featured_image'))
                                    ->columnSpanFull(),
                                FileUpload::make('featured_image')
                                    ->label('Featured Image hochladen/ersetzen')
                                    ->image()
                                    ->imageEditor()
                                    ->disk('public')
                                    ->directory('livewire-tmp')
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->hint('Empfohlen: 16:9 Format')
                                    ->helperText('Wird automatisch in die Mediathek übernommen')
                                    ->columnSpanFull(),
                                MediaRichEditor::make('content')
                                    ->label('Inhalt')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'link',
                                        'h2',
                                        'h3',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'codeBlock',
                                        'undo',
                                        'redo',
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        SeoFormSchema::make(),
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
