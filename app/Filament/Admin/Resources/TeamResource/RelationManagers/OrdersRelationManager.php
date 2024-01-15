<?php

namespace App\Filament\Admin\Resources\TeamResource\RelationManagers;

use App\Events\OrderCreated;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Order ID')
                            ->disabled()
                            ->required()
                            ->default(random_int(100000000, 999999999)),
                        Forms\Components\TextInput::make('order_reference')
                            ->required(),
                        Forms\Components\Select::make('pipeline_id')
                            ->label('Pipeline')
                            ->required()
                            ->preload()
                            ->default(1)
                            ->live()
                            ->relationship('pipeline', 'name'),
                        Forms\Components\Select::make('stage_id')
                            ->label('Stage')
                            ->required()
                            ->options(fn (Forms\Get $get): \Illuminate\Support\Collection => Stage::query()
                                ->where('pipeline_id', $get('pipeline_id'))
                                ->pluck('name', 'id'))
                            ->default(1),
                    ])->columns(2),
                Forms\Components\Section::make('Customer Details')
                    ->schema([
                        Forms\Components\TextInput::make('customer_first_name')
                            ->label('First name')
                            ->required(),
                        Forms\Components\TextInput::make('customer_last_name')
                            ->label('Last name')
                            ->required(),
                        Forms\Components\TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Phone')
                            ->required()
                    ])->columns(2),
                Forms\Components\Section::make('Shipping & Installation')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_address')
                            ->label('Address')
                            ->required(),
                        Forms\Components\Select::make('postal_id')
                            ->label('Postal')
                            ->searchable()
                            ->preload()
                            ->relationship('postal', 'postal')
                            ->required(),
                        Forms\Components\Select::make('city_id')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('country_id')
                            ->required()
                            ->relationship('country', 'name'),
                        Forms\Components\DatePicker::make('installation_date'),
                        Forms\Components\TextInput::make('tracking_code')
                    ])->columns(2)
                    ->description('The shipment will always be send to this address. The installer will also be notified about this address. If the installer needs to install somewhere other than this address – they must be notified elsewhere'),
                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('inventory')
                            ->label('Items in order')
                            ->schema([
                                Forms\Components\Select::make('inventory_id')
                                    ->label('Product')
                                    ->options(
                                        function () {
                                            return
                                                Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                                                    ->pluck('products.detailed_name', 'inventories.id');
                                        }
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->live()
                                    ->readOnly(),
                            ])->columns(2)
                    ])
                ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('shipping_address')
                    ->label('Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_first_name')
                    ->label('Full name')
                    ->formatStateUsing(fn (Order $record) => $record->customer_first_name . ' ' . $record->customer_last_name)
                    ->searchable(),
                Tables\Columns\TextColumn::make('tracking_code')
                    ->label('T&T')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (Order $order) {
                        OrderCreated::dispatch($order);
                    }),
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
