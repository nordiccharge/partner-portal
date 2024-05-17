<?php

namespace App\Filament\Operation\Resources\ChargerResource\Pages;

use App\Filament\Operation\Resources\ChargerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCharger extends EditRecord
{
    protected static string $resource = ChargerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
