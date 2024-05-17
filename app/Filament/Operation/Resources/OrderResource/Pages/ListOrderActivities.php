<?php

namespace App\Filament\Operation\Resources\OrderResource\Pages;

use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListOrderActivities extends ListActivities
{
    protected static string $resource = \App\Filament\Operation\Resources\OrderResource::class;

    public function mount($record): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->record = $this->resolveRecord($record);
    }

}
