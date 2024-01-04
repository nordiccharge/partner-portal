<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListInventoryActivities extends ListActivities
{
    protected static string $resource = InventoryResource::class;

    public function mount($record): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->record = $this->resolveRecord($record);
    }

}
