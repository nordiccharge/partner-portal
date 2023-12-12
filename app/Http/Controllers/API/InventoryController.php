<?php

namespace App\Http\Controllers\API;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Postal;
use App\Models\Team;
use Illuminate\Http\Request;

class InventoryController extends Controller
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
        return response()->json($team->inventories, 200);
    }

}
