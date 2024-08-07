<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private function apiAllowed(Request $request): bool {
        $team = Team::findOrFail($request->header('team'));
        return $team->basic_api && $team->secret_key == $request->header('key');
    }
    public function index(Request $request)
    {
        $key = $request->header('key');
        $team_id = $request->header('team');
        $team = Team::find($team_id);
        if ($team->secret_key == $key) {
            return response()->json(Product::all(['id', 'sku', 'image_url', 'name', 'description']), 200);
        }

        return response()->json('Unauthorized', 401);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        return Product::findOrFail($id)->get(['id', 'sku', 'image_url', 'name', 'description']);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
