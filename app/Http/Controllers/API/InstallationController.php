<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class InstallationController extends Controller
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
        return response()->json($team->installations->map(function ($installation) {
            return [
                'id' => $installation->id,
                'name' => $installation->name,
                'kw' => $installation->kw,
                'price' => $installation->price,
            ];
        }), 200);
    }
}
