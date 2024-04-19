<?php

namespace App\Filament\Admin\Resources\OrderResource\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Models\Order;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice ID'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function ($record) {
                        return match ($record->status) {
                            InvoiceStatus::Pending => 'warning',
                            InvoiceStatus::Sent => 'success',
                        };
                    }),
                Tables\Columns\TextColumn::make('invoiceable.team.company.name')
                    ->label('Company'),
                Tables\Columns\TextColumn::make('total_price')
                    ->suffix(' DKK'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label('View')
                    ->url(fn ($record): string => route('filament.admin.resources.invoices.view', ['record' => $record]))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
