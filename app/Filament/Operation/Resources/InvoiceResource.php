<?php

namespace App\Filament\Operation\Resources;

use App\Enums\InvoiceStatus;
use App\Filament\Operation\Resources\InvoiceResource\Pages;
use App\Filament\Operation\Resources\InvoiceResource\Widgets\AmountDueOverview;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    private static function getActionOrders(): Collection
    {
        return static::getModel()::where('status', '!=', InvoiceStatus::Sent)->get();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getActionOrders()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (static::getActionOrders()->count() > 0) {
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
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\MorphToSelect::make('invoiceable')
                            ->label('Order to Invoice')
                            ->types([
                                Forms\Components\MorphToSelect\Type::make(Order::class)
                                    ->getOptionLabelFromRecordUsing(fn (Order $order) => "Order #{$order->id} – " . $order->team->name),
                                Forms\Components\MorphToSelect\Type::make(PurchaseOrder::class)
                                    ->getOptionLabelFromRecordUsing(fn (PurchaseOrder $purchaseOrder) => "Purchase Order #{$purchaseOrder->id} – " . $purchaseOrder->team->name)
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
                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoiceable_id')
                    ->label('Order ID')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoiceable_type')
                    ->formatStateUsing(function (string $state) {
                        return match ($state) {
                            Order::class => 'Order',
                            PurchaseOrder::class => 'Purchase Order',
                            default => $state,
                        };
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Type'),
                Tables\Columns\TextColumn::make('invoiceable.order_reference')
                    ->label('Reference')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoiceable.shipping_address')
                    ->label('Address')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoiceable.full_name')
                    ->label('Full name')
                    ->searchable([
                        'customer_first_name', 'customer_last_name'
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function ($record) {
                        return match ($record->status) {
                            InvoiceStatus::Pending => 'warning',
                            InvoiceStatus::Sent => 'success',
                        };
                    }),
                Tables\Columns\TextColumn::make('invoiceable.team.company.name')
                    ->label('Company')
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_price')
                    ->suffix(' DKK'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Complete')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->status == InvoiceStatus::Sent)
                    ->action(fn ($record) => $record->update(['status' => InvoiceStatus::Sent])),
                Tables\Actions\Action::make('Cancel')
                    ->color('warning')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->status == InvoiceStatus::Pending)
                    ->action(fn ($record) => $record->update(['status' => InvoiceStatus::Pending])),
                Tables\Actions\Action::make('History')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => InvoiceResource::getUrl('activities', ['record' => $record])),
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
            'activities' => InvoiceResource\Pages\ListInvoiceActivities::route('/{record}/activities'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
