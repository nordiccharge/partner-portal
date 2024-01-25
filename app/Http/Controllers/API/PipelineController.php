<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index() {
        return response()->json(Pipeline::all(['id', 'name']), 200);
    }

    public function store(Request $request, string $id) {
        $pipeline = Pipeline::findOrFail($id);

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
