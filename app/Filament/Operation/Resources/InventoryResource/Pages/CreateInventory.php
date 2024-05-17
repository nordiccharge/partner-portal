<?php

namespace App\Filament\Operation\Resources\InventoryResource\Pages;

use App\Filament\Operation\Resources\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;
}
