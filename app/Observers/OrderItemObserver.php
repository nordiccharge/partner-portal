<?php

namespace App\Observers;

use App\Jobs\MontaJob;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        Log::debug('OrderItem created');
        $new_quantity = $orderItem->inventory->quantity - $orderItem->quantity;
        $inventory = Inventory::find($orderItem->inventory_id);
        $inventory->update(['quantity' => $new_quantity]);
        activity()
            ->performedOn($inventory)
            ->log('Quantity updated from ' . $orderItem->inventory->quantity . ' to ' . $new_quantity . ' on #' . $orderItem->order_id);

        Log::debug('Checking if order is going to Monta Monta: ' . ($orderItem->inventory->product->category_id == 1) . ', ' . ($orderItem->order->action != "" || $orderItem->order->action != null));
        $order = Order::find($orderItem->order_id);
        if ($orderItem->inventory->product->category_id == 1 && ($order->action != "" || $order->action != null)) {
            Log::debug('Dispatching MontaJob');
            MontaJob::dispatch($order, 'false', $orderItem->inventory->product->brand->name)
                ->onQueue('monta-ne')
                ->onConnection('database');
        }
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        //
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $orderItem): void
    {

        $new_quantity = $orderItem->inventory->quantity - $orderItem->quantity;
        Inventory::find($orderItem->inventory_id)->update(['quantity' => $new_quantity]);
    }

    /**
     * Handle the OrderItem "restored" event.
     */
    public function restored(OrderItem $orderItem): void
    {
        //
    }

    /**
     * Handle the OrderItem "force deleted" event.
     */
    public function forceDeleted(OrderItem $orderItem): void
    {
        //
    }
}
