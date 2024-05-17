<?php

namespace App\Filament\Operation\Resources\ChargerResource\Pages;

use App\Filament\Operation\Resources\ChargerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChargers extends ListRecords
{
    protected static string $resource = ChargerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
