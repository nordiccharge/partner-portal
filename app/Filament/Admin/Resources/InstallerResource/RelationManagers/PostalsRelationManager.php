<?php

namespace App\Filament\Admin\Resources\InstallerResource\RelationManagers;

use App\Filament\Imports\InstallerPostalImporter;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\ImportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostalsRelationManager extends RelationManager
{
    protected static string $relationship = 'installerPostals';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('postal_id')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->relationship('postal', 'postal'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('postal')
            ->columns([
                Tables\Columns\TextColumn::make('postal.postal'),
                Tables\Columns\TextColumn::make('postal.city.name'),
                Tables\Columns\TextColumn::make('postal.country.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(InstallerPostalImporter::class)
                    ->options(['installer_id' => (int)$this->getOwnerRecord()->getKey()]),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
