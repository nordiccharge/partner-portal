<?php

namespace App\Filament\Admin\Resources\TeamResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'purchase_orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tracking_code')
                    ->label('T&T')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Section::make('Shipping details')
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
                            ->columns(2)
                    ])->live(),
                Forms\Components\Section::make('Order details')
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
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
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
