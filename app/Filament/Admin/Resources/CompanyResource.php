<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CompanyResource\Pages;
use App\Filament\Admin\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('company_type_id')
                            ->relationship('companyType', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('contact_email')
                            ->email(),
                        Forms\Components\TextInput::make('contact_phone'),
                        Forms\Components\TextInput::make('vat_number')
                            ->label('VAT (CVR)')
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('invoice_email')
                            ->email(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Shipping Details')
                    ->schema([
                        Forms\Components\TextInput::make('sender_name')
                            ->label('Name')
                            ->required(),
                        Forms\Components\TextInput::make('sender_attention')
                            ->label('Attention')
                            ->default('')
                            ->nullable(),
                        Forms\Components\TextInput::make('sender_address')
                            ->label('Address')
                            ->required(),
                        Forms\Components\TextInput::make('sender_address2')
                            ->label('Secondary address')
                            ->default('')
                            ->nullable(),
                        Forms\Components\TextInput::make('sender_zip')
                            ->label('Postcode')
                            ->required(),
                        Forms\Components\TextInput::make('sender_city')
                            ->label('City')
                            ->required(),
                        Forms\Components\TextInput::make('sender_country')
                            ->label('Country')
                            ->required(),
                        Forms\Components\TextInput::make('sender_state')
                            ->label('State')
                            ->nullable(),
                        Forms\Components\TextInput::make('sender_phone')
                            ->label('Phone')
                            ->required()
                            ->nullable(),
                        Forms\Components\TextInput::make('sender_email')
                            ->label('Email')
                            ->required()
                            ->nullable()
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('companyType.name'),
                Tables\Columns\TextColumn::make('contact_email')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TeamsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
