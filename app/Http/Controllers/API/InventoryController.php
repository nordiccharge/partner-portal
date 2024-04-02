<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Team;
use Illuminate\Http\Request;

class InventoryController extends Controller
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
        return response()->json($team->inventories->map(function ($inventory) {
            $inventory_quantity = $inventory->quantity;
            if ($inventory->global) {
                if ($inventory->quantity <= 0) {
                    $inventory_quantity = 0;
                }
                if ($inventory->quantity < 5) {
                    $inventory_quantity = 1;
                }
                if ($inventory->quantity < 10) {
                    $inventory_quantity = 5;
                }
                if ($inventory->quantity < 50) {
                    $inventory_quantity = 10;
                }
                if ($inventory->quantity < 100) {
                    $inventory_quantity = 50;
                };

                return '100+';
            }
            return [
                'id' => $inventory->id,
                'global' => $inventory->global,
                'product_id' => $inventory->product->id,
                'quantity' => $inventory->quantity,
                'sale_price' => $inventory->sale_price,
            ];
        }), 200);
    }
}
