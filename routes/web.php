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
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return redirect('/operation');
        }
        return redirect('/partner');
    });
});

Route::group(['domain' => 'installer.nordiccharge.com'], function(){
    Route::get('/', [\App\Http\Controllers\InstallerController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\InstallerController::class, 'charger']);
});

Route::group(['domain' => 'portal.nordiccharge.local'], function(){
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return redirect('/operation');
        }
        return redirect('/partner');
    });
});

Route::group(['domain' => 'installer.nordiccharge.local'], function(){
    Route::get('/', [\App\Http\Controllers\InstallerController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\InstallerController::class, 'charger']);
});



//Route::get('/import', [\App\Http\Controllers\ImportController:a:class, 'import']);
