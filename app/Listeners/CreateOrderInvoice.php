<?php

namespace App\Listeners;

use App\Enums\StageAutomation;
use App\Models\Invoice;
use App\Models\Order;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateOrderInvoice
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
    public function handle(object $event): void
    {
        $order = $event->order;
        if ($order->stage->automation_type == StageAutomation::Invoice) {
            Log::debug('Creating Invoice for Order by listener');
            Invoice::create([
                'invoiceable_id' => $order->id,
                'invoiceable_type' => Order::class,
                'status' => 'pending'
            ]);
            activity()
                ->performedOn($order)
                ->event('system')
                ->log('Invoice created by stage automation');
        }
    }
}
