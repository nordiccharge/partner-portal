<?php

use App\Models\City;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['domain' => 'portal.nordiccharge.com'], function(){
    Route::any('/', function () {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return redirect('/operation');
        }
        return redirect('/partner');
    });
});

Route::group(['domain' => 'portal.nordiccharge.local'], function(){
    Route::any('/', function () {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return redirect('/operation');
        }
        return redirect('/partner');
    });

    Route::any('/return/{id}', function (string $id) {
        try {
            $order = \App\Models\Order::findOrFail($id);
            return view('returnOrder', ['order' => $order]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return 'Not found';
        }
    });

});
