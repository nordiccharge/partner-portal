<?php

namespace App\Filament\Operation\Resources;

use App\Filament\Operation\Resources\InstallerJobResource\Pages;
use App\Filament\Operation\Resources\InstallerJobResource\RelationManagers;
use App\Models\InstallerJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstallerJobResource extends Resource
{
    protected static ?string $model = InstallerJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationGroup = 'Chargers';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallerJobs::route('/'),
            'create' => Pages\CreateInstallerJob::route('/create'),
            'edit' => Pages\EditInstallerJob::route('/{record}/edit'),
        ];
    }
}
