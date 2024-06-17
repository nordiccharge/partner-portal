<?php

namespace App\Filament\Operation\Resources;

use App\Filament\Exports\OrderExporter;
use App\Filament\Operation\Resources\OrderResource\Pages;
use App\Models\City;
use App\Models\Installation;
use App\Models\Installer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\Postal;
use App\Models\Stage;
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
                            ->options(fn (Forms\Get $get): Collection => Stage::query()
                                ->where('pipeline_id', $get('pipeline_id'))
                                ->pluck('name', 'id'))
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
                                        Forms\Components\Select::make('inventory_id')
                                            ->label('Product')
                                            ->options(
                                                function (Forms\Get $get) {
                                                    return
                                                        Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                                                            ->where('inventories.team_id', '=', (int)$get('../../team_id'))
                                                            ->orWhere('inventories.global', '=', true)
                                                            ->pluck('products.detailed_name', 'inventories.id');
                                                }
                                            )
                                            ->searchable()
                                            ->hidden(fn (Forms\Get $get) => $get('../../team_id') === null)
                                            ->live()
                                            ->required()
                                            ->afterStateUpdated(
                                                function (Forms\Set $set, ?string $state) {
                                                    if ($state) {
                                                        $inventory = Inventory::findOrFail($state);
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
                Tables\Filters\Filter::make('installation_date')
                    ->form([
                        Forms\Components\DatePicker::make('installation_date_from'),
                        Forms\Components\DatePicker::make('installation_date_until'),
                    ])
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
                Tables\Filters\Filter::make('pipeline_stage')
                    ->form([
                        Forms\Components\Select::make('pipeline_id')
                            ->label('Pipeline')
                            ->preload()
                            ->multiple()
                            ->relationship('pipeline', 'name')
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
                Tables\Filters\TernaryFilter::make('tracking_code')
                    ->label('Has Tracking Code')
                    ->placeholder('All Orders')
                    ->nullable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
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
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => OrderResource::getUrl('activities', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
                    ->exporter(OrderExporter::class),
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
