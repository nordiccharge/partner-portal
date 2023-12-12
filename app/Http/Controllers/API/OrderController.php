<?php

namespace App\Http\Controllers\API;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Team;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    private function apiAllowed(Request $request): bool {
        $team = Team::find($request->header('team_id'));
        return $team->basic_api && $team->secret_key == $request->header('secret_key');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(!$this->apiAllowed($request)) {
            return response()->json('Unauthorized', 401);
        }

        $team = Team::find($request->header('team_id'));
        return response()->json($team->orders, 200);
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
                    'quantity' => (int)$product['quantity']
                ]);
            }
        }

        $order = Order::create([
            'team_id' => $request->header('team_id'),
            'id' => random_int(100000000, 999999999),
            'order_reference' => $request->post('order_reference'),
            'pipeline_id' => (int)$request->post('pipeline_id'),
            'stage_id' => (int)$request->post('stage_id'),
            'customer_first_name' => $request->post('customer_first_name'),
            'customer_last_name' => $request->post('customer_last_name'),
            'customer_email' => $request->post('customer_email'),
            'customer_phone' => $request->post('customer_phone'),
            'shipping_address' => $request->post('shipping_address'),
            'postal' => $request->post('postal'),
            'city' => $request->post('city'),
            'country' => $request->post('country'),
            'wished_installation_date' => $request->post('wished_installation_date'),
            'note' => $request->post('note'),
        ]);

        $order->items()->createMany($order_items);

        OrderCreated::dispatch($order);

        return response()->json([
            'message' => 'Success',
            'data' => $order
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

        $team_id = $request->header('team_id');
        $order = Order::find($id);
        $result = [
            'meta' => $order,
            'items' => $order->items()->get(['id', 'order_id', 'inventory_id', 'quantity']),
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
