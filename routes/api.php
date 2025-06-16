<?php

use App\Http\Controllers\PathfinderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Pathfinder API Routes
Route::prefix('pathfinder')->group(function () {
    Route::post('load-data', [PathfinderController::class, 'loadData']);
    Route::post('find-path', [PathfinderController::class, 'findPath']);
    Route::get('nodes', [PathfinderController::class, 'getNodes']);
    Route::get('road-types', [PathfinderController::class, 'getRoadTypes']);
});
