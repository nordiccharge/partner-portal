<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Installation;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\Product;
use App\Models\Stage;
use Filament\Facades\Filament;
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
use Livewire\Attributes\Rule;
use Nette\Utils\Image;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Management';

    private static function getOrdersInProgress(): Collection
    {
        return static::getModel()::whereHas('stage', function (Builder $query) {
            $query->where('state', '!=', 'completed')
            ->where('state', '!=', 'aborted')
            ->where('state', '!=', 'return');
        })->get();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getOrdersInProgress()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getOrdersInProgress()->count() > 0 ? 'info' : 'primary';
    }

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
                            ->relationship('pipeline', 'name', fn(Builder $query, Forms\Get $get) => $query->where('team_id', Filament::getTenant()->id))
                            ->afterStateUpdated(
                                function (Forms\Set $set, ?string $state) {
                                    if ($state) {
                                        $pipeline = Pipeline::findOrFail($state);
                                        $set('nc_price', $pipeline->nc_price);
                                    } else {
                                        $set('nc_price', null);
                                    }
                                }
                            )
                            ->disabledOn('edit'),
                        Forms\Components\TextInput::make('nc_price')
                            ->readOnly()
                            ->hidden(!auth()->user()->isTeamManager())
                            ->label('Nordic Charge Order Flow Price')
                            ->helperText('Excluding taxes')
                            ->suffix('DKK')
                    ])->columns(2),
                Forms\Components\Section::make('Installation Details')
                    ->schema([
                        Forms\Components\Toggle::make('installation_required')
                            ->label('Installation required')
                            ->helperText('If the order requires installation, the installer might be notified about the order and the shipping address')
                            ->default(true),
                        Forms\Components\Select::make('installation_id')
                            ->label('Installation')
                            ->relationship('installation', 'name', fn(Builder $query) => $query->where('team_id', Filament::getTenant()->id))
                            ->disabled(fn (Forms\Get $get) => !$get('installation_required'))
                            ->required(fn (Forms\Get $get) => $get('installation_required'))
                            ->afterStateUpdated(
                                function (Forms\Set $set, ?string $state) {
                                    $installation = Installation::findOrFail($state);
                                    $set('installation_price', $installation->price);
                                }
                            ),
                        Forms\Components\TextInput::make('installation_price')
                            ->label('Installation price')
                            ->hidden(!auth()->user()->isTeamManager())
                            ->readOnly()
                            ->disabled(fn (Forms\Get $get) => !$get('installation_required'))
                            ->required(fn (Forms\Get $get) => $get('installation_required'))
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
                                                return Inventory::all()->filter(
                                                    function ($inventory) {
                                                        if ($inventory->quantity > 0) {
                                                            return true;
                                                        }

                                                        return false;
                                                    })
                                                    ->mapWithKeys(
                                                    function ($inventory) {
                                                        $owner = $inventory->team->name;
                                                        if ($inventory->global == 1) {
                                                            $owner = 'Nordic Charge';
                                                        }
                                                        return [$inventory->id => $owner . ' – ' . $inventory->product->name . ' (' . $inventory->product->sku . ')'];
                                                    }
                                                );
                                            }
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->afterStateUpdated(
                                            function (Forms\Set $set, ?string $state) {
                                                if ($state) {
                                                    $inventory = Inventory::findOrFail($state);
                                                    $set('quantity', 1);
                                                    $set('price', $inventory->sale_price);
                                                } else {
                                                    $set('price', null);
                                                }
                                            }
                                        )
                                        ->columnSpan(5),
                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(function (Forms\Get $get) {
                                            if ($get('inventory_id') > 0) {
                                                $inventory = Inventory::findOrFail($get('inventory_id'));
                                                return $inventory->quantity;
                                            }

                                            return 1;
                                        })
                                        ->helperText(function (Forms\Get $get): string {
                                            if ($get('inventory_id') > 0) {
                                                $inventory = Inventory::findOrFail($get('inventory_id'));
                                                return 'Max: ' . $inventory->quantity;
                                            }
                                            return 'Max: ' . 1;
                                        })
                                        ->afterStateUpdated(function (Forms\Contracts\HasForms $livewire, Forms\Components\TextInput $component) {
                                            $livewire->validateOnly($component->getStatePath());
                                        })
                                        ->live()
                                        ->default(1)
                                        ->required()
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Price per item')
                                        ->required()
                                        ->live()
                                        ->readOnly()
                                        ->columnSpan(2)
                                        ->extraInputAttributes(['style' => 'text-align: right'])
                                        ->suffix('DKK'),
                                ])
                                ->live()
                                ->columns(8)
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
                    ->toggleable()
                    ->color(
                        function (Order $record) {
                            if ($record->stage->state === 'completed') {
                                return 'primary';
                            }
                            if ($record->stage->state === 'aborted' || $record->stage->state === 'return') {
                                return 'gray';
                            }
                            else {
                                return 'info';
                            }
                        }
                    ),
                Tables\Columns\TextColumn::make('shipping_address')
                    ->label('Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full name')
                    ->searchable([
                        'customer_first_name', 'customer_last_name'
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->date()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->multiple()
                    ->relationship('stage', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->visible(auth()->user()->isAdmin())
                    ->url(fn ($record) => OrderResource::getUrl('activities', ['record' => $record])),
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
            'activities' => Pages\ListOrderActivities::route('/{record}/activities'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
