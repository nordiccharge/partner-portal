<?php

namespace App\Observers;

use App\Enums\PipelineAutomation;
use App\Jobs\InstallerJob;
use App\Models\Brand;
use App\Models\Charger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ChargerObserver
{
    public function created(Charger $record): void
    {
        $order = $record->order;
        $pipeline = $order->pipeline;

        try {
            $brand = Brand::find($record->product->brand_id);
            $product = $record->product_id;

            Log::debug("Brand first product: " . $brand->products->first()->id . " Product: " . $product);
            if ($order->installation_required == 1 && ($brand->products->first()->id == $product)) {
                InstallerJob::dispatch($order, $record->id, $order->action)
                    ->onQueue('monta-ne')
                    ->onConnection('database')
                    ->delay(Carbon::now()->addSeconds(10));
            }
        } catch (\Exception $e) {
            Log::debug('Failed to add Monta on charger to Install Tool: ' . $e->getMessage());
            activity()
                ->performedOn($record)
                ->event('system')
                ->log('Failed to add Monta on charger to Install Tool: ' . $e->getMessage());
        }
    }
}
