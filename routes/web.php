<?php

use App\Models\City;
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

Route::get('/', function () {
    if (auth()->check() && auth()->user()->is_admin) {
        return redirect('/admin');
    }
    return redirect('/partner');
});

//Route::get('/import', [\App\Http\Controllers\ImportController::class, 'import']);
