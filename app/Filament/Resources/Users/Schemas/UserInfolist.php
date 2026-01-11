<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Benutzerinformationen')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('email')
                            ->label('E-Mail-Adresse')
                            ->copyable()
                            ->icon('heroicon-o-envelope')
                            ->copyMessage('E-Mail kopiert!')
                            ->copyMessageDuration(1500),

                        TextEntry::make('email_verified_at')
                            ->label('E-Mail-Verifizierung')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Nicht verifiziert')
                            ->badge()
                            ->color(fn($state) => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn($state) => $state ? 'Verifiziert am ' . $state->format('d.m.Y') : 'Nicht verifiziert')
                            ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),

                        TextEntry::make('created_at')
                            ->label('Erstellt am')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Zuletzt aktualisiert')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-clock')
                            ->since(),
                    ])
                    ->columns(2),
            ]);
    }
}
