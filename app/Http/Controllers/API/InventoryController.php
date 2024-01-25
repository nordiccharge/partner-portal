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
            return [
                'id' => $inventory->id,
                'product_id' => $inventory->name,
                'quantity' => $inventory->quantity,
                'sale_price' => $inventory->sale_price,
            ];
        }), 200);
    }
}
