<?php

namespace App\Http\Controllers\API;

use App\Enums\StageAutomation;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Stage;
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
        } catch (\Exception $e) {
            return response()->json('Installation date failed ' . $e->getMessage(), 404);
        }
    }
}
