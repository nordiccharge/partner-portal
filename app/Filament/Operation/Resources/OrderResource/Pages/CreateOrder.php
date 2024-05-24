<?php

namespace App\Filament\Operation\Resources\OrderResource\Pages;

use App\Events\OrderCreated;
use App\Filament\Operation\Resources\OrderResource;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(): void {
        $this->form->fill([
            'id' => random_int(100000000, 999999999),
            'status' => 'Order Created',
            'with_auto' => 1,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id'] = random_int(100000000, 999999999);
        return $data;
    }

    protected function afterCreate(): void {
        if ($this->data['with_auto']) {
            OrderCreated::dispatch($this->record);
        }
    }
}
