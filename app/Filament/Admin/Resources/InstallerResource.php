<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InstallerResource\Pages;
use App\Filament\Admin\Resources\InstallerResource\RelationManagers;
use App\Models\Company;
use App\Models\Installer;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstallerResource extends Resource
{
    protected static ?string $model = Installer::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Flow & Process';
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->label('Company')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->searchable()
                    ->preload()
                    ->options(Company::where('company_type_id', 1)->pluck('name', 'id')),
                Forms\Components\TextInput::make('contact_email')
                    ->required()
                    ->email(),
                Forms\Components\TextInput::make('contact_phone')
                    ->required(),
                Forms\Components\TextInput::make('invoice_email')
                    ->required()
                    ->email(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name'),
                Tables\Columns\TextColumn::make('contact_email'),
                Tables\Columns\TextColumn::make('contact_phone'),
                Tables\Columns\TextColumn::make('invoice_email'),
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
            RelationManagers\PostalsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallers::route('/'),
            'create' => Pages\CreateInstaller::route('/create'),
            'edit' => Pages\EditInstaller::route('/{record}/edit'),
        ];
    }
}
