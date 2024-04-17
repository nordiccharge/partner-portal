<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Spatie\Activitylog\ActivityLogger;

class InstallationDateController extends Controller
{
    public function index(Request $request) {
        $request->validate([
            'order' => 'required',
            'date' => 'required',
        ]);

        try {
            $order = Order::findOrFail($request->order);
            $order->update([
                'installation_date' => $request->date,
            ]);

            activity()
                ->performedOn($order)
                ->event('system')
                ->log('New installation date added from API ' . $request->date);

            return response()->json('Installation date updated', 200);
        } catch (\Exception $e) {
            return response()->json('Order not found', 404);
        }
    }
}
