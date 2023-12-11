<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected static bool $canCreateAnother = false;

    public function mount(): void {
        $this->form->fill([
            'id' => random_int(100000, 999999),
            'status' => 'Order Created',
            'use_dropshipping' => true
        ]);
    }
    protected function getFormActions(): array
    {
        return [];
    }
}
