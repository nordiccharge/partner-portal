<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\ReturnOrder;
use Illuminate\Support\Facades\Log;
use function Psy\debug;

class ReturnOrderObserver
{
    /**
     * Handle the ReturnOrder "created" event.
     */
    public function created(ReturnOrder $returnOrder): void
    {
        //
    }

    /**
     * Handle the ReturnOrder "updated" event.
     */
    public function updated(ReturnOrder $returnOrder): void
    {
        Log::debug('Return observer updated');
        if($returnOrder->state == 'completed') {
            $orderItems = $returnOrder->order->items;
            foreach ($orderItems as $orderItem) {
                $new_quantity = $orderItem->inventory->quantity + $orderItem->quantity;
                $inventory = Inventory::find($orderItem->inventory_id);
                $inventory->update(['quantity' => $new_quantity]);
                activity()
                    ->performedOn($inventory)
                    ->log('Quantity updated from ' . $orderItem->inventory->quantity . ' to ' . $new_quantity . ' on #' . $orderItem->order_id);
            }
        }
    }

    /**
     * Handle the ReturnOrder "deleted" event.
     */
    public function deleted(ReturnOrder $returnOrder): void
    {
        //
    }

    /**
     * Handle the ReturnOrder "restored" event.
     */
    public function restored(ReturnOrder $returnOrder): void
    {
        //
    }

    /**
     * Handle the ReturnOrder "force deleted" event.
     */
    public function forceDeleted(ReturnOrder $returnOrder): void
    {
        //
    }
}
