<?php

namespace App\Filament\Operation\Resources;

use App\Filament\Operation\Resources\InventoryResource\Pages;
use App\Filament\Operation\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use PHPUnit\Metadata\Group;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Logistics';

    protected static ?int $navigationSort = 7;

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
                        ->readOnly()
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
                                        ->integer(),
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
                ])->columns(2),
                Forms\Components\Select::make('global')
                    ->label('Scope')
                    ->options([
                        0 => 'Only show on assigned Team',
                        1 => 'Show on all Teams'
                    ])
                    ->default(0)
                    ->required()
            ])->columns(2);
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
                Tables\Columns\ToggleColumn::make('global')
                    ->label('Global')
                    ->disabled()
                    ->sortable()
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
                Tables\Actions\Action::make('Quantity')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Placeholder::make('helpText')
                            ->label('Your changes will be automatically saved'),
                        Forms\Components\Select::make('quantityMethod')
                            ->label('Action')
                            ->required()
                            ->options([
                                'Add', 'Subtract', 'Move'
                            ])
                            ->live()
                            ->default(0),
                        Forms\Components\Select::make('new_inventory')
                            ->label('Move to')
                            ->required()
                            ->disabled(fn (Forms\Get $get) => $get('quantityMethod') != 2)
                            ->hidden(fn (Forms\Get $get) => $get('quantityMethod') != 2)
                            ->options(fn (Inventory $record) => Inventory::where('product_id', '=', $record->product_id)->where('id', '!=', $record->id)->get()->pluck('id', 'id'))
                            ->reactive()
                            ->preload()
                            ->searchable(),
                        Forms\Components\TextInput::make('newQuantity')
                            ->label('Amount')
                            ->required()
                            ->default(0)
                            ->integer(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->default(''),
                    ])
                    ->action(function (array $data, Inventory $record = null): void {
                        $newQuantity = $record->quantity;
                        $oldQuantity = $record->quantity;
                        if ($data['quantityMethod'] == 0) {
                            $newQuantity = (int)$data['newQuantity'] + (int)$oldQuantity;
                            $record->quantity = (int)$data['newQuantity'] + (int)$oldQuantity;
                        } elseif ($data['quantityMethod'] == 1) {
                            $newQuantity = (int)$oldQuantity - (int)$data['newQuantity'];
                            $record->quantity = (int)$oldQuantity - (int)$data['newQuantity'];
                        }

                        $record->update(['quantity']);
                        activity()
                            ->performedOn($record)
                            ->causedBy(auth()->user())
                            ->withProperties(['manual' => true, 'reason' => $data['reason']])
                            ->log('Manually updated from ' . $oldQuantity . ' to ' . $newQuantity . ' by ' . auth()->user()->email);
                    }),
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => InventoryResource::getUrl('activities', ['record' => $record])),
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
