<?php

namespace App\Filament\Admin\Resources\PipelineResource\RelationManagers;

use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StagesRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->string(),
                Forms\Components\Select::make('order')
                    ->required()
                    ->default(0)
                    ->options(range(0, 20))
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('state')
                    ->options([
                        'step' => 'Step',
                        'aborted' => 'Aborted',
                        'completed' => 'Completed',
                        'action' => 'Action needed'
                    ])
                    ->default('step')
                    ->required()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('order'),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Stage $record): string => $record->description ?: 'No description'),
                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'step' => 'gray',
                        'aborted' => 'danger',
                        'completed' => 'success',
                        'action' => 'info'
                    })
            ])->defaultSort('order')
            ->filters([
                //
            ])
            ->headerActions([
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
