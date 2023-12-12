<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListInventoryActivities extends ListActivities
{
    protected static string $resource = InventoryResource::class;

    protected static bool $isDiscovered = false;

    public function mount($record): void
    {
        if (auth()->user()->isAdmin()) {
            $this->record = $this->resolveRecord($record);
        } else {
            $this->record = null;
        }
    }
}
