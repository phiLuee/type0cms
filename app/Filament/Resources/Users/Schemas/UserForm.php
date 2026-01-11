<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Benutzerinformationen')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('name'),

                        TextInput::make('email')
                            ->label('E-Mail-Adresse')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->autocomplete('email')
                            ->prefixIcon('heroicon-o-envelope'),

                        TextInput::make('password')
                            ->label('Passwort')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->revealable()
                            ->autocomplete('new-password')
                            ->helperText('Mindestens 8 Zeichen. Bei Bearbeitung: Leer lassen, um das aktuelle Passwort beizubehalten.')
                            ->prefixIcon('heroicon-o-key'),

                        DateTimePicker::make('email_verified_at')
                            ->label('E-Mail verifiziert am')
                            ->displayFormat('d.m.Y H:i:s')
                            ->seconds(false)
                            ->native(false)
                            ->prefixIcon('heroicon-o-check-circle'),
                    ])
                    ->columns(2),
            ]);
    }
}
