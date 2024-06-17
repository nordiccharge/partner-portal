<?php

namespace App\Filament\Operation\Resources\OrderResource\Pages;

use App\Events\OrderCreated;
use App\Filament\Operation\Resources\OrderResource;
use App\Models\Postal;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Traits\LogsActivity;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(): void {
        $this->form->fill([
            'id' => random_int(100000000, 999999999),
            'status' => 'Order Created',
            'with_auto' => 1,
            'created_at' => now()
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id'] = random_int(100000000, 999999999);
        $geo_data = app('geocoder')->geocode($data['geocoding'])->get()->first();
        $data['shipping_address'] = $geo_data->getStreetName() . ' ' . $geo_data->getStreetNumber();
        $postal = Postal::where('postal', $geo_data->getPostalCode())->first();
        $data['postal_id'] = $postal->id;
        $data['city_id'] = $postal->city_id;
        $data['country_id'] = $postal->city->country_id;
        return $data;
    }

    protected function afterCreate(): void {
        if ($this->data['with_auto']) {
            activity()
                ->performedOn($this->record)
                ->event('system')
                ->log('Order created with automations');
            OrderCreated::dispatch($this->record);
        } else {
            activity()
                ->performedOn($this->record)
                ->event('system')
                ->log('Order created WITHOUT automations');
        }
    }
}
