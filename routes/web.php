<?php

use App\Http\Controllers\PathfinderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PathfinderController::class, 'index'])->name('pathfinder');

// API Routes as backup
Route::prefix('api/pathfinder')->group(function () {
    Route::post('load-data', [PathfinderController::class, 'loadData']);
    Route::post('find-path', [PathfinderController::class, 'findPath']);
    Route::get('nodes', [PathfinderController::class, 'getNodes']);
    Route::get('road-types', [PathfinderController::class, 'getRoadTypes']);
});
