<?php

namespace App\Filament\Operation\Resources\PurchaseOrderResource\Pages;

use App\Filament\Operation\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;
}
