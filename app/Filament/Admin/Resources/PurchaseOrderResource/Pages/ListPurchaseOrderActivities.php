<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
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
