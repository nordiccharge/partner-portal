<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChargerResource\Pages;
use App\Filament\Resources\ChargerResource\RelationManagers;
use App\Models\Charger;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChargerResource extends Resource
{
    protected static ?string $model = Charger::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Management';


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
                Tables\Columns\ImageColumn::make('product.image_url')
                    ->label(''),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.customer_first_name')
                    ->label('Full name')
                    ->formatStateUsing(fn (Charger $record) => $record->order->customer_first_name . ' ' . $record->order->customer_last_name)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.shipping_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.id'),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(auth()->user()->isAdmin()),
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
            'index' => Pages\ListChargers::route('/'),
            'create' => Pages\CreateCharger::route('/create'),
            'edit' => Pages\EditCharger::route('/{record}/edit'),
        ];
    }
}
