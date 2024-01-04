<?php

namespace App\Http\Controllers\API;

use App\Events\OrderFulfilled;
use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\Order;
use App\Models\Product;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

class ShipmentController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json('error', 401);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = json_decode($request->getContent());
        $data = $response->data[0];
        $order_id = $data->order->orderNo;
        $order = Order::findOrFail($order_id);
        $team = $order->team;
        $team_id = $team->id;

        // Check Team settings
        if ($team->shipping_api_get != 1) {
            return response()->json('Error. Fulfillment not allowed', 401);
        }


        $tracking_numbers = [];
        $chargers = [];

        foreach ($data->packages as $package) {
            array_push($tracking_numbers, $package->trackingNumber);

            foreach($package->lines as $line) {
                foreach ($line->serialNumbers as $serialNumber) {
                    $sku = $line->orderLine->item->sku;
                    $product = Product::where('sku', '=', $sku)->first();

                    if ($product === null) {
                        Log::error('Product with SKU ' . $sku . ' not found');
                        return response()->json('Error. Product with SKU ' . $sku . ' not found', 401);
                    } else {
                        Log::debug('Creating charger with SKU ' . $sku . ' and serial number ' . $serialNumber . ' for order ' . $order_id . ' and team ' . $team_id);
                        Charger::create([
                            'team_id' => $team_id,
                            'order_id' => $order_id,
                            'product_id' => $product->id,
                            'serial_number' => $serialNumber,
                        ]);
                    }
                    array_push($chargers, ['sku' => $sku, 'serialNumber' => $serialNumber]);
                }
            }
        }

        try {
            Log::debug('Adding tracking code to ' . implode(', ', $tracking_numbers) . ' on '. $order_id);
            $order->update(['tracking_code' => implode(', ', $tracking_numbers)]);
        } catch (\Exception $e) {
            Log::error('Caught exception on emailNotification()' . $e->getMessage());
        }

        OrderFulfilled::dispatch(Order::find($order_id));

        return response()->json([
                'message' => 'Success. Chargers created!',
                'data' => [
                    'orderNo' => $order_id,
                    'chargers' => $chargers,
                    'trackingNumbers' => $tracking_numbers
                ]
        ], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return response()->json('error', 401);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        return response()->json('error', 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
