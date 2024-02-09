<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InventoryResource\Pages;
use App\Filament\Admin\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PHPUnit\Metadata\Group;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Global Operations';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->reactive()
                    ->live(),
                Forms\Components\Select::make('product_id')
                    ->preload()
                    ->label('Product')
                    ->searchable()
                    ->required()
                    ->options(
                        Product::all()->pluck('detailed_name', 'id')
                    ),

                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('sale_price')
                        ->required()
                        ->suffix('DKK'),
                    Forms\Components\TextInput::make('quantity')
                        ->required()
                        ->live(true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('quantity', $state))
                        ->default(0)
                        ->disabled()
                        ->helperText('Your changes will be automatically saved')
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('addNewQuantity')
                                ->icon('heroicon-m-arrow-path')
                                ->form([
                                    Forms\Components\Placeholder::make('helpText')
                                        ->label('Your changes will be automatically saved'),
                                    Forms\Components\Select::make('quantityMethod')
                                        ->label('')
                                        ->options([
                                            'Add', 'Subtract'
                                        ])
                                        ->default(0),
                                    Forms\Components\TextInput::make('newQuantity')
                                        ->label('Amount')
                                        ->default(0)
                                        ->integer()
                                ])
                                ->action(function (Forms\Set $set, array $data, Inventory $record = null): void {
                                    if ($record == null) {
                                        $set('quantity', (int)$data['newQuantity']);
                                        return;
                                    }
                                    $newQuantity = $record->quantity;
                                    $oldQuantity = $record->quantity;
                                    if ($data['quantityMethod'] == 0) {
                                        $newQuantity = (int)$data['newQuantity'] + (int)$oldQuantity;
                                        $record->quantity = (int)$data['newQuantity'] + (int)$oldQuantity;
                                    } elseif ($data['quantityMethod'] == 1) {
                                        $newQuantity = (int)$oldQuantity - (int)$data['newQuantity'];
                                        $record->quantity = (int)$oldQuantity - (int)$data['newQuantity'];
                                    }
                                    $set('quantity', $newQuantity);

                                    $record->update(['quantity']);
                                    activity()
                                        ->performedOn($record)
                                        ->log('Quantity updated from ' . $oldQuantity . ' to ' . $newQuantity . ' by ' . auth()->user()->email);
                                })->requiresConfirmation()
                        )
                ])->columns(2)
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('product.image_url')
                    ->label(''),
                Tables\Columns\TextColumn::make('product.name'),
                Tables\Columns\TextColumn::make('product.sku')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Price')
                    ->money('DKK')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team')
                    ->label('Team')
                    ->multiple()
                    ->relationship('team', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('product')
                    ->label('Product')
                    ->multiple()
                    ->relationship('product', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
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
