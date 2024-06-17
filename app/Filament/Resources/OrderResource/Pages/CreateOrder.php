<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Events\OrderCreated;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Postal;
use App\Models\Stage;
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
        $data['created_at'] = now();
        $data['stage_id'] = Stage::where('pipeline_id', '=', $data['pipeline_id'])->where('order', '=', 1)->first()->id;
        $geo_data = app('geocoder')->geocode($data['geocoding'])->get()->first();
        $data['shipping_address'] = $geo_data->getStreetName() . ' ' . $geo_data->getStreetNumber();
        $postal = Postal::where('postal', $geo_data->getPostalCode())->first();
        $data['postal_id'] = $postal->id;
        $data['city_id'] = $postal->city_id;
        $data['country_id'] = $postal->city->country_id;
        return $data;
    }

    protected function afterCreate(): void {
        OrderCreated::dispatch($this->record);
    }
}
