<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\Admin\Resources\InvoiceResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    public function infolist(Infolist $infolist): Infolist {
        return $infolist
            ->schema([
                Section::make('Information')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(function ($record) {
                                return match ($record->status) {
                                    InvoiceStatus::Pending => 'warning',
                                    InvoiceStatus::Sent => 'success',
                                };
                            }),
                        TextEntry::make('invoiceable.team.company.name')
                            ->label('Company')
                            ->copyable(),
                        TextEntry::make('invoiceable.team.company.vat_number')
                            ->label('VAT (CVR)')
                            ->copyable(),
                        TextEntry::make('invoiceable.team.company.invoice_email')
                            ->label('Invoice email')
                            ->copyable(),
                        TextEntry::make('note'),
                        Section::make('Order Details')
                            ->schema([
                                TextEntry::make('invoiceable.id')
                                    ->label('Order ID from Nordic Charge')
                                    ->copyable(),
                                TextEntry::make('invoiceable.order_reference')
                                    ->label('Order reference from customer')
                                    ->copyable(),
                                TextEntry::make('invoiceable.created_at')
                                    ->label('Order date')
                                    ->copyable(),
                                TextEntry::make('invoiceable.installer.company.name')
                                    ->label('Installer'),
                                TextEntry::make('full_name')
                                    ->label('Full name')
                                    ->default(fn ($record) => $record->invoiceable->customer_first_name . ' ' . $record->invoiceable->customer_last_name)
                                    ->copyable(),
                                TextEntry::make('shipping_address')
                                    ->label('Address')
                                    ->default(fn ($record) => $record->invoiceable->shipping_address . ', ' . $record->invoiceable->postal->postal . ' ' . $record->invoiceable->city->name . ' ' . $record->invoiceable->country->name)
                                    ->copyable(),
                            ])->columns(3),
                    ])
                    ->columns(2)
                    ->hidden(fn ($record) => !$record->invoiceable instanceof Order),
                Section::make('Invoice')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('title')
                                    ->copyable(),
                                TextEntry::make('quantity'),
                                TextEntry::make('price')
                                    ->label('Price per item')
                                    ->copyable()
                                    ->suffix(' DKK'),
                                TextEntry::make('description')
                            ])->columns(3),
                        TextEntry::make('total_price')
                            ->label('Total price')
                            ->suffix(' DKK')
                            ->copyable(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('History')
                ->icon('heroicon-o-document-text')
                ->link()
                ->url(fn ($record) => InvoiceResource::getUrl('activities', ['record' => $record])),
            Actions\EditAction::make(),
            Actions\Action::make('Complete invoice')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->hidden(fn ($record) => $record->status == InvoiceStatus::Sent)
                ->action(function ($record) {
                    $record->update(['status' => InvoiceStatus::Sent]);
                    $this->redirect(InvoiceResource::getUrl());
                }),
            Actions\Action::make('Cancel invoice')
                ->color('warning')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->hidden(fn ($record) => $record->status == InvoiceStatus::Pending)
                ->action(function ($record) {
                    $record->update(['status' => InvoiceStatus::Pending]);
                    $this->redirect(InvoiceResource::getUrl('index', ['activeTab' => 'paid']));
                }),
        ];
    }
}
