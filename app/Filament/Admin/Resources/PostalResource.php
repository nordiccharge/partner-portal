<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PostalResource\Pages;
use App\Filament\Admin\Resources\PostalResource\RelationManagers;
use App\Filament\Exports\PostalExporter;
use App\Filament\Imports\PostalImporter;
use App\Models\Installer;
use App\Models\Postal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PhpParser\Node\Expr\Ternary;
use PHPUnit\Util\Filter;
use Illuminate\Database\Eloquent\Collection;

class PostalResource extends Resource
{
    protected static ?string $model = Postal::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Flow & Process';
    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('postal')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('country_id')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->relationship('country', 'name'),
                Forms\Components\Select::make('city_id')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->relationship('city', 'name'),
                Forms\Components\Select::make('installer_id')
                    ->label('Primary Installer')
                    ->required()
                    ->nullable()
                    ->searchable()
                    ->options(
                        function (Forms\Get $get) {
                            return Installer::join('installer_postals', 'installers.id', '=', 'installer_postals.installer_id')
                                ->where('installer_postals.postal_id', $get('id'))
                                ->join('companies', 'installers.company_id', '=', 'companies.id')
                                ->pluck('companies.name', 'installers.id');
                        }
                    )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(PostalImporter::class)
            ])
            ->columns([
                Tables\Columns\TextColumn::make('postal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('installer.company.name')
                    ->label('Primary Installer')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('installer')
                    ->label('Has Installer')
                    ->attribute('installer_id')
                    ->placeholder('With and without')
                    ->nullable(),
                Tables\Filters\SelectFilter::make('installer_id')
                    ->label('Installer')
                    ->options(
                        function (Forms\Get $get) {
                            return
                                Installer::join('companies', 'installers.company_id', '=', 'companies.id')
                                    ->pluck('companies.name', 'installers.id');
                        }
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('postal'),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(PostalExporter::class),
                    Tables\Actions\BulkAction::make('set_installer')
                        ->label('Set Primary Installer')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Forms\Components\Select::make('installer_id')
                                ->label('Primary Installer')
                                ->required()
                                ->nullable()
                                ->searchable()
                                ->preload()
                                ->options(
                                    function () {
                                        return Installer::join('companies', 'installers.company_id', '=', 'companies.id')
                                            ->pluck('companies.name', 'installers.id');
                                    }
                                )
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['installer_id' => $data['installer_id']])),
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
            'index' => Pages\ListPostals::route('/'),
            'create' => Pages\CreatePostal::route('/create'),
            'edit' => Pages\EditPostal::route('/{record}/edit'),
        ];
    }
}
