<?php

namespace App\Http\Controllers;

use App\Models\Charger;
use Illuminate\Http\Request;

class InstallerController extends Controller
{
    public function index()
    {
        return view('installer.index');
    }

    public function charger(Request $request)
    {
        $serial = $request->post('serial');
        $charger = Charger::where('serial_number', $serial);
        if ($charger->count() == 0) {
            return view('installer.index')->with('error', 'Laderen blev ikke fundet');
        }
        $charger = $charger->first();
        $product = $charger->product;
        if ($product->sku == 'ZM000688') {
            return view('installer.chargers.zaptecgo', ['product' => $product, 'charger' => $charger]);
        }
        return view('installer.charger');
    }
}
