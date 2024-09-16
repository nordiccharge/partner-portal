<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['domain' => 'portal.nordiccharge.com'], function(){
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('orders', \App\Http\Controllers\API\OrderController::class);
    Route::apiResource('products', \App\Http\Controllers\API\ProductController::class);
    Route::get('pipelines', [\App\Http\Controllers\API\PipelineController::class, 'index']);
    Route::get('pipelines/{id}', [\App\Http\Controllers\API\PipelineController::class, 'show']);
    Route::get('inventory', [\App\Http\Controllers\API\InventoryController::class, 'index']);
    Route::get('installations', [\App\Http\Controllers\API\InstallationController::class, 'index']);
    Route::post('shipping', [\App\Http\Controllers\API\ShipmentController::class, 'store']);
    Route::post('date', [\App\Http\Controllers\API\InstallationDateController::class, 'index']);
    Route::post('installer', [\App\Http\Controllers\API\InstallationDateController::class, 'completeOrder']);

});

if (App::environment('local')) {
    Route::group(['domain' => 'portal.nordiccharge.local'], function(){
        Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('orders', \App\Http\Controllers\API\OrderController::class);
        Route::apiResource('products', \App\Http\Controllers\API\ProductController::class);
        Route::get('pipelines', [\App\Http\Controllers\API\PipelineController::class, 'index']);
        Route::get('pipelines/{id}', [\App\Http\Controllers\API\PipelineController::class, 'show']);
        Route::get('inventory', [\App\Http\Controllers\API\InventoryController::class, 'index']);
        Route::get('installations', [\App\Http\Controllers\API\InstallationController::class, 'index']);
        Route::post('shipping', [\App\Http\Controllers\API\ShipmentController::class, 'store']);
        Route::post('date', [\App\Http\Controllers\API\InstallationDateController::class, 'index']);
        Route::post('date', [\App\Http\Controllers\API\InstallationDateController::class, 'completeOrder']);

    });
}
