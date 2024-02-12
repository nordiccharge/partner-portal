<?php

namespace App\Filament\Admin\Resources;

use App\Enums\InvoiceStatus;
use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Filament\Admin\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Global Operations';

    private static function getActionOrders(): Collection
    {
        return static::getModel()::where('status', '!=', InvoiceStatus::Paid)->get();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getActionOrders()->count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\MorphToSelect::make('invoiceable')
                            ->label('Order to Invoice')
                            ->types([
                                Forms\Components\MorphToSelect\Type::make(Order::class)
                                    ->titleAttribute('id'),
                                Forms\Components\MorphToSelect\Type::make(PurchaseOrder::class)
                                    ->titleAttribute('id')
                            ])
                            ->preload()
                            ->required()
                            ->searchable()
                            ->disabledOn('edit'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(InvoiceStatus::class)
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label('Note')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
                Forms\Components\Section::make('Invoice Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                Forms\Components\TextInput::make('price')
                                    ->label('Price per item')
                                    ->numeric()
                                    ->suffix('DKK')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->columnSpanFull()
                                    ->rows(3)
                                    ->nullable()
                            ])->columns(3)
                    ])
                    ->disabledOn('create')
                    ->hiddenOn('create')
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoiceable_id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('invoiceable_type')
                    ->formatStateUsing(function (string $state) {
                        return match ($state) {
                            Order::class => 'Order',
                            PurchaseOrder::class => 'Purchase Order',
                            default => $state,
                        };
                    })
                    ->label('Type'),
                Tables\Columns\SelectColumn::make('status')
                    ->options(InvoiceStatus::class),
                Tables\Columns\TextColumn::make('total_price')
                    ->suffix(' DKK')
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
