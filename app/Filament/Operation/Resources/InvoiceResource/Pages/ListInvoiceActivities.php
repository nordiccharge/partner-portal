<?php

namespace App\Filament\Operation\Resources\InvoiceResource\Pages;

use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListInvoiceActivities extends ListActivities
{
    protected static string $resource = \App\Filament\Operation\Resources\InvoiceResource::class;

    public function mount($record): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->record = $this->resolveRecord($record);
    }

}
