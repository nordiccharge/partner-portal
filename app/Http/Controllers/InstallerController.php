<?php

namespace App\Http\Controllers;

use App\Models\Charger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstallerController extends Controller
{
    public function index()
    {
        Log::debug('Index');
        return view('installer.index');
    }

    public function charger(Request $request)
    {
        Log::debug('Charger');
        $serial = $request->post('serial');
        $charger = Charger::where('serial_number', $serial);
        if ($charger->count() == 0) {
            return view('installer.index')->with('error', 'Laderen blev ikke fundet');
        }
        $charger = $charger->first();
        $product = $charger->product;
        return view('installer.chargers.zaptecgo', ['product' => $product, 'charger' => $charger]);
    }
}
