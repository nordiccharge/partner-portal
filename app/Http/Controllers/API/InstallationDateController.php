<?php

namespace App\Http\Controllers\API;

use App\Enums\StageAutomation;
use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Stage;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\ActivityLogger;

class InstallationDateController extends Controller
{
    public function index(Request $request) {
        $request->validate([
            'order' => 'required',
            'date' => 'required',
        ]);

        try {
            $order = Order::findOrFail((int)$request->order);
            if ($order->installation_required === true || $order->installation_required == 1) {
                $installation_stage = Stage::where('pipeline_id', (int)$order->pipeline_id)->where('automation_type', StageAutomation::InstallationDateConfirmed)->first();
                $new_stage = $order->stage;
                if ($order->stage->order <= $installation_stage->order) {
                    $new_stage = $installation_stage;
                }
                $order->update([
                    'installation_date' => $request->date,
                    'stage_id' => $new_stage->id,
                ]);
                activity()
                    ->performedOn($order)
                    ->event('system')
                    ->log('New installation date added from API ' . $request->date);

                return response()->json('Installation date updated', 200);
            } else {
                return response()->json('Installation not required', 404);
            }
        } catch (\Exception $e) {
            return response()->json('Installation date failed ' . $e->getMessage(), 404);
        }
    }

    public function completeOrder(Request $request) {
        $request->validate([
            'serial_number' => 'required',
        ]);

        try {
            $order = Charger::where('serial_number', $request->serial_number)->first()->order;
            $order->update([
                'stage_id' => Stage::where('pipeline_id', $order->pipeline_id)->where('state', 'completed')->first()->id,
                'installation_date' => now()
            ]);
            Invoice::create([
                'invoiceable_id' => $order->id,
                'invoiceable_type' => Order::class,
                'status' => 'pending'
            ]);
            activity()
                ->performedOn($order)
                ->event('system')
                ->log('Installation completed from Installer Tool');
            activity()
                ->performedOn($order)
                ->event('system')
                ->log('Invoice created by Installer Tool');

            return response()->json('Installation completed', 200);
        } catch (\Exception $e) {
            return response()->json('Installation completion failed ' . $e->getMessage(), 404);
        }
    }
}
