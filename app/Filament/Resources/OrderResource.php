<?php

namespace App\Filament\Resources;

use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\City;
use App\Models\Installation;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\Postal;
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
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Rule;
use Nette\Utils\Html;
use Nette\Utils\Image;
use RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;

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
                            ->relationship('pipeline', 'name', fn(Builder $query) => $query->where('team_id', Filament::getTenant()->id))
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
                        Forms\Components\Select::make('geocoding')
                            ->label('Search for address')
                            ->required()
                            ->searchable()
                            ->visibleOn('create')
                            ->live()
                            ->columnSpanFull()
                            ->getSearchResultsUsing(function ($query) {
                                return app('geocoder')->geocode($query)->get()
                                    ->mapWithKeys(fn ($result) => [
                                        $result->getFormattedAddress() => $result->getFormattedAddress()
                                    ])
                                    ->toArray();
                            }),
                        Forms\Components\TextInput::make('shipping_address')
                            ->label('Address')
                            ->visibleOn('edit')
                            ->required(),
                        Forms\Components\Select::make('postal_id')
                            ->label('Postal')
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                $postal = Postal::find($state);
                                $set('city_id', $postal->city_id);
                                $set('country_id', $postal->city->country_id);
                            })
                            ->live()
                            ->reactive()
                            ->visibleOn('edit')
                            ->relationship('postal', 'postal')
                            ->required(),
                        Forms\Components\Select::make('city_id')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visibleOn('edit')
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                $city = City::find($state);
                                $set('postal_id', null);
                                $set('country_id', $city->country_id);
                            })
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->label('Country')
                            ->required()
                            ->visibleOn('edit')
                    ])->columns(2)
                    ->description('The shipment will always be send to this address. The installer will also be notified about this address. If the installer needs to install somewhere other than this address – they must be notified elsewhere'),
                    Forms\Components\Section::make('Order Items')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->label('Items in order')
                                ->relationship()
                                ->schema([
                                    RecordFinder::make('inventory_id')
                                        ->label('Product')
                                        ->standalone()
                                        ->tableQuery(Inventory::query())
                                        ->query(function (Builder $query) {
                                            return Inventory::where('quantity', '>', 0)
                                                ->where('team_id', '=', Filament::getTenant()->id)
                                                ->orWhere('global', '=', true);
                                        })
                                        ->live()
                                        ->required()
                                        ->afterStateUpdated(
                                            function (Forms\Set $set, ?string $state) {
                                                if ($state) {
                                                    $inventory = Inventory::findOrFail($state);
                                                    $set('price', $inventory->sale_price);
                                                    $set('quantity', 1);
                                                } else {
                                                    $set('price', null);
                                                }
                                            }
                                        )
                                        ->openModalActionLabel('Select product')
                                        ->getRecordLabelFromRecordUsing(fn (Inventory $record) => "{$record->product->name} ({$record->product->sku})")
                                        ->tableColumns([
                                            Tables\Columns\ImageColumn::make('product.image_url')
                                                ->label(''),
                                            Tables\Columns\TextColumn::make('team.name')
                                                ->label('Team')
                                                ->searchable()
                                                ->sortable(),
                                            Tables\Columns\TextColumn::make('product.name')
                                                ->searchable()
                                                ->description(fn (Inventory $record): string => $record->product->description ?: 'No description'),
                                            Tables\Columns\TextColumn::make('product.sku')
                                                ->label('SKU')
                                                ->searchable()
                                                ->toggleable()
                                                ->sortable(),
                                            Tables\Columns\TextColumn::make('quantity')
                                                ->badge()
                                                ->color(function ($record) {
                                                    $quantity = $record->quantity;
                                                    if ($quantity > 0) {
                                                        return 'success';
                                                    }

                                                    if ($quantity < 0) {
                                                        return 'danger';
                                                    }

                                                    return 'primary';
                                                })
                                                ->sortable(),
                                            Tables\Columns\TextColumn::make('sale_price')
                                                ->label('Price')
                                                ->money('DKK')
                                                ->sortable()
                                                ->searchable(),
                                        ])
                                        ->inline()
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
                        ]),
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
                Tables\Filters\Filter::make('installation_date')
                    ->form([
                        Forms\Components\DatePicker::make('installation_date_from'),
                        Forms\Components\DatePicker::make('installation_date_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['installation_date_from'],
                                fn (Builder $query, $date): Builder => $query->where('team_id', '=', Filament::getTenant()->id)->whereDate('installation_date', '>=', $date),
                            )
                            ->when(
                                $data['installation_date_until'],
                                fn (Builder $query, $date): Builder => $query->where('team_id', '=', Filament::getTenant()->id)->whereDate('installation_date', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('pipeline_stage')
                    ->form([
                        Forms\Components\Select::make('pipeline_id')
                            ->label('Pipeline')
                            ->preload()
                            ->multiple()
                            ->relationship('pipeline', 'name', fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id))
                            ->searchable()
                            ->live(),
                        Forms\Components\Select::make('stage_name')
                            ->label('Stage')
                            ->options(function (Forms\Get $get) {
                                $stages = [];
                                foreach ($get('pipeline_id') as $pipeline_id) {
                                    foreach (Pipeline::findOrFail($pipeline_id)->stages as $stage) {
                                        $stages[$stage->name] = $stage->name;
                                    }
                                }
                                return array_unique($stages);
                            })
                            ->searchable()
                            ->multiple(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['pipeline_id'],
                                function (Builder $query, $pipeline_id_array): Builder {
                                    $query->whereIn('pipeline_id', $pipeline_id_array);
                                    return $query;
                                }
                            )
                            ->when(
                                $data['stage_name'],
                                function (Builder $query, $stage_name_array): Builder {
                                    $query->whereIn('stage_id', Stage::whereIn('name', $stage_name_array)->pluck('id'));
                                    return $query;
                                }
                            );
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->where('team_id', '=', Filament::getTenant()->id)->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->where('team_id', '=', Filament::getTenant()->id)->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->visible(auth()->user()->isAdmin())
                    ->url(fn ($record) => OrderResource::getUrl('activities', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(OrderExporter::class),
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
