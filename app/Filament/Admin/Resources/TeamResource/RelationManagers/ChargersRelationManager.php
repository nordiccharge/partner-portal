<?php

namespace App\Filament\Admin\Resources\TeamResource\RelationManagers;

use App\Models\Charger;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChargersRelationManager extends RelationManager
{
    protected static string $relationship = 'chargers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Order')
                    ->preload()
                    ->searchable()
                    ->options(Order::all()->pluck('id', 'id')->toArray())
                    ->nullable(),
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->preload()
                    ->searchable()
                    ->options(\App\Models\Product::all()->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\TextInput::make('serial_number')
                    ->nullable()
                    ->maxLength(255),
                Forms\Components\TextInput::make('service')
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
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