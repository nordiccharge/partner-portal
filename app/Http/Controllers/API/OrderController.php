<?php

namespace App\Http\Controllers\API;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Installation;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\Postal;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    private function apiAllowed(Request $request): bool {
        $team = Team::findOrFail($request->header('team'));
        return $team->basic_api && $team->secret_key == $request->header('key');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {

        if(!$this->apiAllowed($request)) {
            return response()->json('Unauthorized', 401);
        }

        $team = Team::findOrFail($request->header('team'));
        return response()->json($team->orders->map(function ($order) {
            return [
                'id' => $order->id,
                'team_id' => $order->team_id,
                'pipeline_id' => $order->pipeline_id,
                'stage_id' => $order->stage_id,
                'order_reference' => $order->order_reference,
                'note' => $order->note,
                'customer_first_name' => $order->customer_first_name,
                'customer_last_name' => $order->customer_last_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'postal' => $order->postal->postal,
                'city' => $order->city->name,
                'country' => $order->country->short_name,
                'installation_required' => $order->installation_required,
                'wished_installation_date' => $order->wished_installation_date,
                'installation_id' => $order->installation_id,
                'installation_price' => $order->installation_price,
                'installation_date' => $order->installation_date,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        }), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    public function store(Request $request)
    {

        if(!$this->apiAllowed($request)) {
            return response()->json('Unauthorized', 401);
        }

        $order_items = array();
        if(!$request->post('items') == null || '') {
            $products = $request->post('items');
            $products = json_decode($products, true);
            foreach($products as $product) {
                array_push($order_items, [
                    'inventory_id' => (int)$product['id'],
                    'quantity' => (int)$product['quantity'],
                    'price' => Inventory::findOrFail((int)$product['id'])->sale_price,
                ]);
            }
        }

        $order = null;

        $pipeline = Pipeline::findOrFail((int)$request->post('pipeline_id'));

        if ($request->post('installation_required') == 1) {
            $order = Order::create([
                'team_id' => $request->header('team'),
                'id' => random_int(100000000, 999999999),
                'order_reference' => $request->post('order_reference'),
                'pipeline_id' => $pipeline->id,
                'nc_price' => $pipeline->nc_price,
                'stage_id' => Stage::where('pipeline_id', '=', $pipeline->id)->where('order', '=', 1)->first()->id,
                'customer_first_name' => $request->post('customer_first_name'),
                'customer_last_name' => $request->post('customer_last_name'),
                'customer_email' => $request->post('customer_email'),
                'customer_phone' => $request->post('customer_phone'),
                'shipping_address' => $request->post('shipping_address'),
                'postal_id' => Postal::where('postal', '=', $request->post('postal'))->first()->id,
                'city_id' => City::where('name', '=', $request->post('city'))->first()->id,
                'country_id' => Country::where('short_name', '=', $request->post('country'))->first()->id,
                'installation_required' => $request->post('installation_required'),
                'wished_installation_date' => $request->post('wished_installation_date'),
                'installation_id' => (int)$request->post('installation_id'),
                'installation_price' => Installation::findOrFail((int)$request->post('installation_id'))->price,
                'note' => $request->post('note'),
            ]);

        }   else {
            $order = Order::create([
                'team_id' => $request->header('team'),
                'id' => random_int(100000000, 999999999),
                'order_reference' => $request->post('order_reference'),
                'pipeline_id' => $pipeline->id,
                'nc_price' => $pipeline->nc_price,
                'stage_id' => (int)$request->post('stage_id'),
                'customer_first_name' => $request->post('customer_first_name'),
                'customer_last_name' => $request->post('customer_last_name'),
                'customer_email' => $request->post('customer_email'),
                'customer_phone' => $request->post('customer_phone'),
                'shipping_address' => $request->post('shipping_address'),
                'postal_id' => Postal::where('postal', '=', $request->post('postal'))->first()->id,
                'city_id' => City::where('name', '=', $request->post('city'))->first()->id,
                'country_id' => Country::where('short_name', '=', $request->post('country'))->first()->id,
                'note' => $request->post('note'),
            ]);

        }
        activity()
            ->performedOn($order)
            ->event('system')
            ->log('Order created by API');

        $order->items()->createMany($order_items);
        OrderCreated::dispatch($order);

        unset($order['team']);
        return response()->json([
            'message' => 'Success',
            'data' => [
                'id' => $order->id,
                'team_id' => $order->team_id,
                'pipeline_id' => $order->pipeline_id,
                'stage_id' => $order->stage_id,
                'order_reference' => $order->order_reference,
                'note' => $order->note,
                'customer_first_name' => $order->customer_first_name,
                'customer_last_name' => $order->customer_last_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'postal' => $order->postal->postal,
                'city' => $order->city->name,
                'country' => $order->country->short_name,
                'installation_required' => $order->installation_required,
                'wished_installation_date' => $order->wished_installation_date,
                'installation_id' => $order->installation_id,
                'installation_price' => $order->installation_price,
                'installation_date' => $order->installation_date,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]
            ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        if(!$this->apiAllowed($request)) {
            return response()->json('Unauthorized', 401);
        }

        $team_id = $request->header('team');
        $order = Order::find($id);
        $result = [
            'order' => [
                    'id' => $order->id,
                    'team_id' => $order->team_id,
                    'pipeline_id' => $order->pipeline_id,
                    'stage_id' => $order->stage_id,
                    'nc_price' => $order->nc_price,
                    'order_reference' => $order->order_reference,
                    'note' => $order->note,
                    'customer_first_name' => $order->customer_first_name,
                    'customer_last_name' => $order->customer_last_name,
                    'customer_email' => $order->customer_email,
                    'customer_phone' => $order->customer_phone,
                    'shipping_address' => $order->shipping_address,
                    'postal' => $order->postal->postal,
                    'city' => $order->city->name,
                    'country' => $order->country->short_name,
                    'installation_required' => $order->installation_required,
                    'wished_installation_date' => $order->wished_installation_date,
                    'installation_id' => $order->installation_id,
                    'installation_price' => $order->installation_price,
                    'installation_date' => $order->installation_date,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ],
            'items' => $order->items()->get(['id', 'inventory_id', 'quantity', 'price']),
        ];

        if ($order->team_id == $team_id) {
            return response()->json($result, 200);
        }

        return null;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if(!$this->apiAllowed($request)) {
            return response()->json('Unauthorized', 401);
        }

        return null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if(!$this->apiAllowed($request)) {
            return response()->json('Unauthorized', 401);
        }

        return null;
    }
}
