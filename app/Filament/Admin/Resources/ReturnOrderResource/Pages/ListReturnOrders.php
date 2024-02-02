<?php

namespace App\Filament\Admin\Resources\ReturnOrderResource\Pages;

use App\Filament\Admin\Resources\ReturnOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnOrders extends ListRecords
{
    protected static string $resource = ReturnOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
