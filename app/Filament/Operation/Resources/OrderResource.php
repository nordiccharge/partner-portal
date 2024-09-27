<?php

namespace App\Filament\Operation\Resources;

use App\Filament\Exports\OrderExporter;
use App\Filament\Exports\OrderItemsExporter;
use App\Filament\Operation\Resources\OrderResource\Pages;
use App\Models\City;
use App\Models\Installation;
use App\Models\Installer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pipeline;
use App\Models\Postal;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Team;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Support\HtmlString;
use Livewire\Pipe;
use RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;
use SendGrid\Mail\Section;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 1;

    private static function getActionOrders(): Collection
    {
        return static::getModel()::whereHas('stage', function (Builder $query) {
            $query->where('state', '!=', 'completed')
            ->where('state', '!=', 'aborted')
            ->where('state', '!=', 'return');
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
        $isCreate = $form->getOperation() === "create";
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
                        Forms\Components\DatePicker::make('created_at')
                            ->label('Created At')
                            ->required(),
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
                            ->relationship('pipeline', 'name', fn(Builder $query, Forms\Get $get) => $query->where('team_id', '=', $get('team_id')))
                            ->searchable()
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
                            ->live(),
                        Forms\Components\Select::make('stage_id')
                            ->label('Stage')
                            ->required()
                            ->options(
                                function (Forms\Get $get) {
                                    if (!$get('pipeline_id')) {
                                        return [];
                                    }
                                    return
                                        Stage::where('pipeline_id', $get('pipeline_id'))
                                            ->orderBy('order')
                                            ->get()
                                            ->pluck('order_name', 'id');
                                }
                            )
                            ->searchable()
                            ->default(1),
                        Forms\Components\TextInput::make('nc_price')
                            ->label('Nordic Charge Order Flow Price')
                            ->helperText('Excluding taxes')
                            ->suffix('DKK'),
                        Forms\Components\Textarea::make('note')
                            ->columnSpanFull()
                    ])->columns(2),
                    Forms\Components\Section::make('Shipping & Installation Details')
                        ->schema([
                            Forms\Components\TextInput::make('tracking_code')
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('installation_required')
                                ->label('Installation required')
                                ->default(true)
                                ->helperText('If the order requires installation, the installer might be notified about the order and the shipping address'),
                            Forms\Components\Select::make('installation_id')
                                ->label('Installation')
                                ->relationship('installation', 'name', fn(Builder $query, Forms\Get $get) => $query->where('team_id', '=', $get('team_id')))
                                ->disabled(fn (Forms\Get $get) => !$get('installation_required'))
                                ->nullable()
                                ->required(fn (Forms\Get $get) => $get('installation_required'))
                                ->afterStateUpdated(
                                    function (Forms\Set $set, ?string $state) {
                                        $installation = Installation::findOrFail($state);
                                        $set('installation_price', $installation->price);
                                    }
                                ),
                            Forms\Components\TextInput::make('installation_price')
                                ->label('Installation price')
                                ->disabled(fn (Forms\Get $get) => !$get('installation_required'))
                                ->required(fn (Forms\Get $get) => $get('installation_required')),
                            Forms\Components\Select::make('installer_id')
                                ->label('Installer')
                                ->options(
                                    function (Forms\Get $get) {
                                        return
                                            Installer::join('companies', 'installers.company_id', '=', 'companies.id')
                                                ->pluck('companies.name', 'installers.id');
                                    }
                                )
                                ->disabled(fn (Forms\Get $get) => !$get('installation_required')),
                            Forms\Components\DatePicker::make('wished_installation_date')
                                ->label('Wished installation date')
                                ->disabled(fn (Forms\Get $get) => !$get('installation_required')),
                            Forms\Components\DatePicker::make('installation_date')
                                ->label('Installation date')
                                ->disabled(fn (Forms\Get $get) => !$get('installation_required')),
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
                                ->live()
                                ->columnSpanFull()
                                ->visibleOn('create')
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
                        ])
                        ->columnSpanFull(),
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
                                            ->query(function (Builder $query, Forms\Get $get) {
                                                return Inventory::where('quantity', '>', 0)
                                                    ->where('team_id', '=', (int)$get('../../team_id'))
                                                    ->orWhere('global', '=', true);
                                            })
                                            ->hidden(fn (Forms\Get $get) => $get('../../team_id') === null)
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
                                            ->maxValue(function (Forms\Get $get) use ($isCreate) {
                                                if ($get('inventory_id') > 0 && $isCreate) {
                                                    $inventory = Inventory::findOrFail($get('inventory_id'));
                                                    return $inventory->quantity;
                                                }

                                                if ($isCreate) {
                                                    return 0;
                                                }

                                                return null;
                                            })
                                            ->helperText(function (Forms\Get $get) use ($isCreate): string {
                                                if ($get('inventory_id') > 0 && $isCreate) {
                                                    $inventory = Inventory::findOrFail($get('inventory_id'));
                                                    return 'Max: ' . $inventory->quantity;
                                                }

                                                if ($isCreate) {
                                                    return 'Max: ' . 0;
                                                }

                                                return '';
                                            })
                                            ->afterStateUpdated(function (Forms\Contracts\HasForms $livewire, Forms\Components\TextInput $component) use ($isCreate) {
                                                if ($isCreate) {
                                                    $livewire->validateOnly($component->getStatePath());
                                                }
                                            })
                                            ->live()
                                            ->required()
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('price')
                                            ->columnSpan(2)
                                            ->required()
                                            ->extraInputAttributes(['style' => 'text-align: right'])
                                            ->suffix('DKK'),
                                    ])
                                    ->live()
                                    ->default([])
                                    ->columns(8)
                            ])
                            ->columnSpanFull()
                            ->disabled(fn (Forms\Get $get) => $get('team_id') === null),
                Forms\Components\Toggle::make('with_auto')
                    ->label('Create order with automations')
                    ->hiddenOn('edit')
                    ->helperText('Disabling automations will disable: Customer email, pushing shipment, assigning a new installer, sending email to installer and creating order invoice (if set as completed). The quantity will still be deducted from inventory.')
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('installation_date')
                    ->label('Installation Date')
                    ->date()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order_reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                            if ($record->stage->state === 'step' || $record->stage->state === 'return') {
                                return 'gray';
                            }
                        }
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('installer.company.name')
                    ->label('Installer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shipping_address')
                    ->label('Address')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full name')
                    ->searchable([
                        'customer_first_name', 'customer_last_name'
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('team.name')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('postal.postal')
                    ->label('Postal')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->sortable()
                    ->date()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team')
                    ->multiple()
                    ->relationship('team', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('installer_id')
                    ->label('Installer')
                    ->options(
                        function (Forms\Get $get) {
                            return
                                Installer::join('companies', 'installers.company_id', '=', 'companies.id')
                                    ->pluck('companies.name', 'installers.id');
                        }
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('pipeline_stage')
                    ->form([
                        Forms\Components\Select::make('pipeline_id')
                            ->label('Pipeline')
                            ->preload()
                            ->multiple()
                            ->reactive()
                            ->relationship('pipeline', 'name')
                            ->options(function (Forms\Get $get) {
                                if (!$get('../team')['values']) {
                                    return Pipeline::all()->pluck('name', 'id');
                                } else {
                                    $pipelines = [];
                                    foreach ($get('../team')['values'] as $team_id) {
                                        Log::debug('Team ID:' . $team_id);
                                        $team = Team::findOrFail($team_id);
                                        foreach ($team->pipelines as $pipeline) {
                                            $pipelines[$pipeline->id] = $team->name . ":\r\n" . $pipeline->name;
                                        }
                                    }
                                    foreach (Pipeline::where('team_id', null)->get() as $pipeline) {
                                        $pipelines[$pipeline->id] = "Global:\r\n" . $pipeline->name;
                                    }
                                    return $pipelines;
                                }
                            })
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
                    ->columns(2)
                    ->columnSpanFull()
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
                Tables\Filters\TernaryFilter::make('tracking_code')
                    ->label('Has Tracking Code')
                    ->placeholder('All Orders')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('chargers')
                    ->placeholder('All Orders')
                    ->nullable()
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('chargers'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('chargers'),
                        blank: fn (Builder $query): Builder => $query,
                    )
                    ->label('Has charger(s)'),
                Tables\Filters\TernaryFilter::make('invoices')
                    ->placeholder('All Orders')
                    ->nullable()
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('invoices'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('invoices'),
                        blank: fn (Builder $query): Builder => $query,
                    )
                    ->label('Has invoice(s)'),
                Tables\Filters\Filter::make('installation_date')
                    ->form([
                        Forms\Components\DatePicker::make('installation_date_from'),
                        Forms\Components\DatePicker::make('installation_date_until'),
                    ])
                    ->columnSpanFull()
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['installation_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('installation_date', '>=', $date),
                            )
                            ->when(
                                $data['installation_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('installation_date', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('items')
                    ->multiple()
                    ->label('Product items')
                    ->query(
                        function(Builder $query, array $data) {
                            if(!empty($data['values'])) {
                                $query->whereHas('items', function (Builder $query) use ($data) {
                                    $query->whereHas('inventory', function (Builder $query) use ($data) {
                                        $query->whereIn('product_id', $data['values']);
                                    });
                                });
                            }
                        }
                    )
                    ->columnSpanFull()
                    ->options(Product::all()->pluck('detailed_name', 'id'))
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => OrderResource::getUrl('activities', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(OrderExporter::class)
                        ->label('Export to Excel'),
                    Tables\Actions\BulkAction::make('export-json')
                        ->label('Export to JSON')
                        ->icon('heroicon-o-code-bracket-square')
                    ->action(function (Collection $records) {

                        return response()->streamDownload(function () use ($records) {
                            $data = [];
                            foreach ($records as $order) {
                                $data[$order->id] = [
                                    'order_id' => $order->id,
                                    'order_reference' => $order->order_reference,
                                    'team_name' => $order->team->name,
                                    'pipeline_name' => $order->pipeline->name,
                                    'stage_name' => $order->stage->name,
                                    'installer_name' => $order->installer->company->name ?? null,
                                    'shipping_address' => $order->shipping_address,
                                    'postal' => $order->postal->postal,
                                    'city' => $order->city->name,
                                    'country' => $order->city->country->name,
                                    'customer_full_name' => $order->full_name,
                                    'customer_phone' => $order->customer_phone,
                                    'customer_email' => $order->customer_email,
                                    'items' => $order->items->map(function ($item) {
                                            return [
                                                'product_name' => $item->inventory->product->name ?? null,
                                                'sku' => $item->inventory->product->sku ?? null,
                                                'quantity' => $item->quantity,
                                                'price' => $item->price,
                                            ];
                                        }) ?? [],
                                    'chargers' => $order->chargers->map(function ($charger) {
                                            return [
                                                'product_name' => $charger->product->name ?? null,
                                                'serial_number' => $charger->serial_number ?? null,
                                            ];
                                        }) ?? [],
                                    'created_at' => $order->created_at,
                                ];
                            }
                            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                        }, "orders_" . time() . '.json');
                    })
                ])
                ->label('Export Orders'),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            OrderResource\RelationManagers\ChargersRelationManager::class,
            OrderResource\RelationManagers\InvoicesRelationManager::class,
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
