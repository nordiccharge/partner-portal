<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use SendGrid\Mail\Section;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Global Operations';
    protected static ?int $navigationSort = 2;

    private static function getNonFulfilledOrders(): Collection
    {
        return static::getModel()::where('status', '!=', 'completed')->get();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getNonFulfilledOrders()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (static::getNonFulfilledOrders()->count() === 0) {
            return 'primary';
        } else {
            return 'info';
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status')
                    ->helperText('You can set whatever status you want to. Except "completed" or "aborted" will prevent further changes to the purchase order and notify the customer about that it is completed.')
                    ->default('pending'),
                Forms\Components\Textarea::make('note')
                    ->rows(3)
                    ->nullable(),
                Forms\Components\Section::make('Shipping')
                    ->schema([
                        Forms\Components\Toggle::make('use_dropshipping')
                            ->label('Use dropshipping')
                            ->helperText('Send the order to the Nordic Charge warehouse for shipping')
                            ->default(true)
                            ->reactive(),
                        Forms\Components\TextInput::make('tracking_code')
                            ->nullable(),
                        Forms\Components\Section::make('Shipping Details')
                            ->schema([
                                Forms\Components\TextInput::make('shipping_address')
                                    ->nullable(),
                                Forms\Components\TextInput::make('postal')
                                    ->nullable(),
                                Forms\Components\TextInput::make('city')
                                    ->nullable(),
                                Forms\Components\TextInput::make('country')
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->hidden(fn (Forms\Get $get) => $get('use_dropshipping'))
                            ->columnSpanFull(),
                    ])->columns(2),
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
                    ])->columns(2),

            ])->columns(1);
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
                    ->color(function ($record) {
                        if ($record->status === 'completed' || 'fulfilled' || 'aborted' || 'cancelled') {
                            return 'gray';
                        } else {
                            return 'warning';
                        }
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('note'),
                Tables\Columns\TextColumn::make('team.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->color('success')
                    ->action(fn ($record) => $record->update(['status' => 'completed'])),
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => PurchaseOrderResource::getUrl('activities', ['record' => $record])),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'activities' => Pages\ListPurchaseOrderActivities::route('/{record}/activities'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
