<?php

namespace App\Filament\Operation\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\Operation\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'unpaid' => Tab::make('Unpaid invoices')
                ->badge(fn () => Invoice::all()->where('status', '!=', InvoiceStatus::Sent)->count())
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('status', '!=', InvoiceStatus::Sent);
                }),
            'paid' => Tab::make('Sent invoices')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('status', InvoiceStatus::Sent);
                }),
            ];
    }
}
