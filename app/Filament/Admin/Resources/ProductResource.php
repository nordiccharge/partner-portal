<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Faker\Provider\Text;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Items & Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(false)
                    ->columnSpanFull()
                    ->helperText('Choose if the product can be purchased on Purchase Orders. Making a product inactive will not remove it from team inventories.'),
                Forms\Components\Section::make('Product Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('SKU'),
                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Textarea::make('description')
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image_url')
                            ->directory('products')
                            ->image()
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Product Economy')
                    ->schema([
                        Forms\Components\TextInput::make('retail_price')
                            ->numeric()
                            ->nullable()
                            ->helperText('Excluding taxes')
                            ->suffix('DKK')
                            ->default(0),
                        Forms\Components\TextInput::make('purchase_price')
                            ->numeric()
                            ->nullable()
                            ->helperText('Excluding taxes')
                            ->suffix('DKK')
                            ->default(0),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Product Stock')
                    ->schema([
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
                                    ->action(function (Forms\Set $set, array $data, Product $record = null): void {
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
                            ),
                        Forms\Components\Textarea::make('delivery_information')
                            ->helperText('This will be shown at purchase orders and can be used to inform the B2B customer about delivery times and such.'),
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label(''),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Product $record): string => $record->description ?: 'No description'),
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
                    }),
                Tables\Columns\TextColumn::make('brand.name'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('retail_price')
                    ->money('dkk'),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->money('dkk'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
