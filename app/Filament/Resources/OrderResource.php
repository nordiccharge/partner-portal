<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\Product;
use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nette\Utils\Image;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Management';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Order ID')
                            ->disabled()
                            ->required(),
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
                            ->options(fn (Forms\Get $get): Collection => Stage::query()
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
                            ->relationship('country', 'name')
                    ])->columns(2)
                    ->description('The shipment will always be send to this address. The installer will also be notified about this address. If the installer needs to install somewhere other than this address – they must be notified elsewhere'),
                    Forms\Components\Section::make('Order Items')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->label('Items in order')
                                ->relationship()
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
                                        ->required()
                                ])->columnSpanFull()
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order_reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stage.name')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shipping_address')
                    ->label('Address'),
                Tables\Columns\TextColumn::make('customer_first_name')
                    ->label('Full name')
                    ->formatStateUsing(fn (Order $record) => $record->customer_first_name . ' ' . $record->customer_last_name)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(auth()->user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(auth()->user()->isAdmin()),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
