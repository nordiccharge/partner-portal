<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PipelineResource\Pages;
use App\Filament\Admin\Resources\PipelineResource\RelationManagers;
use App\Models\Pipeline;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PipelineResource extends Resource
{
    protected static ?string $model = Pipeline::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Backend';
    protected static ?int $navigationSort = 14;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('team_id')
                            ->label('Team')
                            ->relationship('team', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->autofocus()
                            ->required(),
                        Forms\Components\TextInput::make('nc_price')
                            ->label('Nordic Charge Order Price')
                            ->numeric()
                            ->helperText('Excluding taxes')
                            ->suffix('DKK')
                            ->default(0),
                        Forms\Components\Select::make('automation_type')
                            ->options([
                                'none' => 'None',
                                'auto' => 'Auto',
                                'manual' => 'Manual'
                            ]),
                    ]),
                Forms\Components\Section::make('Shipping')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_type'),
                        Forms\Components\TextInput::make('shipping_price')
                            ->default(0)
                            ->numeric()
                            ->helperText('Excluding taxes')
                            ->suffix('DKK'),
                    ])

                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('team.name'),
                Tables\Columns\ToggleColumn::make('shipping')
                    ->disabled(),
                Tables\Columns\TextColumn::make('stages_count')
                    ->label('Stages')
                    ->counts('stages'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(auth()->user()->isAdmin()),
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
            RelationManagers\StagesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPipelines::route('/'),
            'create' => Pages\CreatePipeline::route('/create'),
            'edit' => Pages\EditPipeline::route('/{record}/edit'),
        ];
    }
}
