<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use PHPUnit\Metadata\Group;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $tenantRelationshipName = 'purchase_orders';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Account';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Order')
                        ->description('Review your basket')
                        ->icon('heroicon-m-shopping-bag')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->label('Items in order')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Product')
                                        ->options(
                                            function () {
                                                return
                                                    Product::all()->where('is_active', '=', 1)->pluck('detailed_name', 'id');
                                            }
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->afterStateUpdated(
                                            function ($state, Forms\Set $set) {
                                                $set('delivery_information', Product::find($state)->delivery_information);
                                            }
                                        ),
                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->required(),
                                    Forms\Components\TextInput::make('delivery_information')
                                        ->disabled()
                                        ->columnSpanFull()
                                        ->hidden(
                                            fn (Forms\Get $get): bool => $get('product_id') == null || ''
                                        ),
                                ])
                                ->live()
                                ->columns(2),
                            Forms\Components\Textarea::make('note')
                                ->nullable()
                                ->columnSpanFull(),
                        ])->live(),
                    Forms\Components\Wizard\Step::make('Delivery')
                        ->description('Let us know where to deliver')
                        ->icon('heroicon-m-truck')
                        ->schema([
                            Forms\Components\Toggle::make('use_dropshipping')
                                ->required()
                                ->default(true),
                            Forms\Components\Group::make([
                                Forms\Components\TextInput::make('shipping_address'),
                                Forms\Components\TextInput::make('city'),
                                Forms\Components\TextInput::make('postal'),
                                Forms\Components\Select::make('country')
                                    ->options(
                                        ['DK' => 'Denmark']
                                    )
                            ])
                            ->hidden(
                            fn (Forms\Get $get): bool => $get('use_dropshipping') == true
                            )
                            ->columns(2),
                        ])
                        ->live(),
                ])
                    ->columnSpanFull()
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="md"
                        >
                            Submit Purchase Order
                        </x-filament::button>
                    BLADE))),
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
