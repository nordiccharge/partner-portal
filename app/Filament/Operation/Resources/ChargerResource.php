<?php

namespace App\Filament\Operation\Resources;

use App\Events\TicketCreated;
use App\Filament\Operation\Resources\ChargerResource\Pages;
use App\Filament\Operation\Resources\ChargerResource\RelationManagers;
use App\Models\Charger;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChargerResource extends Resource
{
    protected static ?string $model = Charger::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Chargers';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Order')
                    ->preload()
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.image_url')
                    ->label(''),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.customer_first_name')
                    ->label('Full name')
                    ->formatStateUsing(fn (Charger $record) => $record->order->customer_first_name . ' ' . $record->order->customer_last_name)
                    ->searchable([
                        'customer_first_name',
                        'customer_last_name',])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.shipping_address')
                    ->label('Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable()
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
