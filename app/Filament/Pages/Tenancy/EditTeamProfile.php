<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditTeamProfile extends EditTenantProfile
{

    public static function getLabel(): string
    {
        return 'Team Settings';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Team Details')
                    ->schema([
                        TextInput::make('name')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Select::make('user_id')
                            ->label('Owner')
                            ->required()
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                    ])
                    ->columns(2),
                Section::make('Team Credentials')
                    ->schema([
                        TextInput::make('id')
                            ->label('Team ID')
                            ->disabled()
                            ->readOnly(),
                        TextInput::make('secret_key')
                            ->helperText('The secret key can be used for invites and API calls')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->disabled()
                            ->readOnly(true),
                    ])
                    ->columns(2),
            ]);
    }
}
