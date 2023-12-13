<?php

namespace App\Filament\Admin\Resources\TeamResource\RelationManagers;

use App\Models\Inventory;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class InventoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(Product::query()->select([DB::raw("CONCAT(name, '  –  ', sku) as name"), 'id'])->pluck('name', 'id')),
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
                                if ($data['quantityMethod'] == 0) {
                                    $newQuantity = (int)$data['newQuantity'] + (int)$record->quantity;
                                    $record->quantity = (int)$data['newQuantity'] + (int)$record->quantity;
                                } elseif ($data['quantityMethod'] == 1) {
                                    $newQuantity = (int)$record->quantity - (int)$data['newQuantity'];
                                    $record->quantity = (int)$record->quantity - (int)$data['newQuantity'];
                                }
                                $set('quantity', $newQuantity);

                                $record->update(['quantity']);
                            })->requiresConfirmation()
                    )
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.sku')
            ->columns([
                Tables\Columns\ImageColumn::make('product.image_url')
                    ->label(''),
                Tables\Columns\TextColumn::make('product.id')
                    ->label('PID'),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('sale_price')
                    ->money('DKK')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
