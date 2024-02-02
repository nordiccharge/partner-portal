<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use App\Models\Team;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    private function apiAllowed(Request $request): bool {
        $team = Team::findOrFail($request->header('team'));
        return $team->basic_api && $team->secret_key == $request->header('key');
    }
    public function index(Request $request) {
        $request->header('key');
        if(!$this->apiAllowed($request)) {
            return response()->json('Unauthorized', 401);
        }
        $team = Team::findOrFail($request->header('team'));
        return response()->json($team->pipelines->map(fn ($pipeline) => ['id' => $pipeline->id, 'name' => $pipeline->name]), 200);
    }

    public function show(Request $request, string $id) {
        if(!$this->apiAllowed($request)) {
            return response()->json('Unauthorized', 401);
        }

        $team = Team::findOrFail($request->header('team'));

        $pipeline = Pipeline::findOrFail($id);

        if($pipeline->team_id != $team->id) {
            return response()->json('Unauthorized', 401);
        }

        return response()->json([
            'id' => $pipeline->id,
            'name' => $pipeline->name,
            'stages' => $pipeline->stages->map(function($stage) {
                return [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'order' => $stage->order,
                ];
            })
        ], 200);
    }
}
