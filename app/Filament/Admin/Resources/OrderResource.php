<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use SendGrid\Mail\Section;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Global Operations';
    protected static ?int $navigationSort = 1;

    private static function getActionOrders(): Collection
    {
        return static::getModel()::whereHas('stage', function (Builder $query) {
            $query->where('state', '=', 'action');
        })->get();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getActionOrders()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (static::getActionOrders()->count() > 4) {
            return 'warning';
        }
        elseif (static::getActionOrders()->count() === 0) {
            return 'primary';
        }
        else {
            return 'info';
        }
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Team Details')
                    ->schema([
                        Forms\Components\Select::make('team_id')
                            ->label('Team')
                            ->relationship('team', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->reactive()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('items', [])),
                    ]),
                Forms\Components\Group::make([Forms\Components\Section::make('Order Details')
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
                        Forms\Components\Textarea::make('note')
                            ->columnSpanFull()
                    ])->columns(2),
                    Forms\Components\Section::make('Shipping & Installation Details')
                        ->schema([
                            Forms\Components\TextInput::make('tracking_code')
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('installation_required')
                                ->label('Installation required')
                                ->helperText('If the order requires installation, the installer might be notified about the order and the shipping address')
                                ->default(false),
                            Forms\Components\Select::make('installation_id')
                                ->label('Installation')
                                ->relationship('installation', 'name')
                                ->disabled(fn (Forms\Get $get) => !$get('installation_required'))
                                ->required(fn (Forms\Get $get) => $get('installation_required')),
                        ])->live()
                        ->columns(2),
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
                        ])
                        ->columnSpanFull(),
                        Forms\Components\Section::make('Order Items')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->label('Items in order')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('inventory_id')
                                            ->label('Product')
                                            ->options(
                                                function (Forms\Get $get) {
                                                    return
                                                        Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                                                            ->where('inventories.team_id', '=', (int)$get('../../team_id'))
                                                            ->pluck('products.detailed_name', 'inventories.id');
                                                }
                                            )
                                            ->searchable()
                                            ->hidden(fn (Forms\Get $get) => $get('../../team_id') === null)
                                            ->live()
                                            ->required(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                    ])
                                    ->live()
                                    ->default([])
                                    ->columns(2)
                            ])
                            ->columnSpanFull()
                            ->disabled(fn (Forms\Get $get) => $get('team_id') === null),
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
                    ->color(
                        function (Order $record) {
                            if ($record->stage->state === 'action') {
                                return 'info';
                            }
                            if ($record->stage->state === 'completed') {
                                return 'success';
                            }
                            if ($record->stage->state === 'aborted') {
                                return 'danger';
                            }
                            if ($record->stage->state === 'step') {
                                return 'gray';
                            }
                        }
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shipping_address')
                    ->label('Address'),
                Tables\Columns\TextColumn::make('customer_first_name')
                    ->label('Full name')
                    ->formatStateUsing(fn (Order $record) => $record->customer_first_name . ' ' . $record->customer_last_name)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('team.name')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('postal_id')
                    ->label('Postal')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team')
                    ->multiple()
                    ->relationship('team', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('stage')
                    ->multiple()
                    ->relationship('stage', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => OrderResource::getUrl('activities', ['record' => $record])),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'activities' => OrderResource\Pages\ListOrderActivities::route('/{record}/activities'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}