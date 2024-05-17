<?php

namespace App\Filament\Operation\Resources\PurchaseOrderResource\Pages;

use App\Filament\Operation\Resources\PurchaseOrderResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListPurchaseOrderActivities extends ListActivities
{
    protected static string $resource = PurchaseOrderResource::class;

    public function mount($record): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->record = $this->resolveRecord($record);
    }

}
