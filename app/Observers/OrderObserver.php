<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Team;
use Filament\Panel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the OrderController "creating" event.
     */
    public function creating(Order $order): void
    {

    }

    /**
     * Handle the OrderController "created" event.
     */
    public function created(Order $order): void
    {

    }

    /**
     * Handle the OrderController "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the OrderController "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the OrderController "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the OrderController "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
