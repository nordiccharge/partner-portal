<?php

namespace App\Listeners;

use App\Models\Postal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AssignOrderInstaller
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
        Log::debug('Adding Installer to Order by listener');
        if ($order->installation_required && $order->installation_id != null) {
            $order->update(['installer_id' => Postal::findOrFail($order->postal->id)->installer_id]);
        }
    }
}
