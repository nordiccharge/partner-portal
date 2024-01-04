<?php

namespace App\Filament\Admin\Resources\InventoryResource\Pages;

use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListInventoryActivities extends ListActivities
{
    protected static string $resource = \App\Filament\Admin\Resources\InventoryResource::class;

    public function mount($record): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->record = $this->resolveRecord($record);
    }

}
