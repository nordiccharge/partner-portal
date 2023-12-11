<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(): void {
        $this->form->fill([
            'id' => random_int(100000000, 999999999),
            'status' => 'Order Created',
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id'] = random_int(100000000, 999999999);
        return $data;
    }
}
