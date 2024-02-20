<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $label = 'product';
    protected static ?string $slug = 'inventory';

    protected static ?string $navigationGroup = 'Account';

    public static function getPluralLabel(): string
    {
        return 'products';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->preload()
                    ->searchable()
                    ->disabledOn('edit')
                    ->required()
                    ->options(
                        Product::all()->pluck('detailed_name', 'id')
                    ),

                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->disabledOn('edit')
                    ->integer(),
                Forms\Components\TextInput::make('sale_price')
                    ->disabledOn('edit')
                    ->required()
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.image_url')
                    ->label(''),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->description(fn (Inventory $record): string => $record->product->description ?: 'No description'),
                Tables\Columns\TextColumn::make('product.sku'),
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
                    ->state(
                        function (Inventory $inventory) {
                            if ($inventory->global) {
                                if ($inventory->quantity <= 0) {
                                    return '0';
                                }
                                if ($inventory->quantity < 5) {
                                    return '1+';
                                }
                                if ($inventory->quantity < 10) {
                                    return '5+';
                                }
                                if ($inventory->quantity < 50) {
                                    return '10+';
                                }
                                if ($inventory->quantity < 100) {
                                    return '50+';
                                };
                            }

                            return $inventory->quantity;
                        }
                    )
                    ->visible(auth()->user()->isTeamManager() || auth()->user()->isAdmin()),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Price')
                    ->money('DKK')
                    ->visible(auth()->user()->isTeamManager() || auth()->user()->isAdmin()),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->visible(auth()->user()->isAdmin())
                    ->url(fn ($record) => InventoryResource::getUrl('activities', ['record' => $record])),
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
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'activities' => Pages\ListInventoryActivities::route('/{record}/activities'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
