<?php

namespace App\Listeners;

use App\Events\OrderFulfilled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderFulfilledNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderFulfilled $event): void
    {
        $order = $event->order;
        activity()
            ->performedOn($order)
            ->event('system')
            ->log('Order fulfilled by shipping company');
    }
}
