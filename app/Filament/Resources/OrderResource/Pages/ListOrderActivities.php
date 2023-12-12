<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListOrderActivities extends ListActivities
{
    protected static string $resource = OrderResource::class;

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
