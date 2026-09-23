<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'abilities:tally-agent'])->prefix('tally-sync')->group(function () {
    Route::get('pending', [App\Http\Controllers\Api\TallySyncApiController::class, 'pending'])->name('api.tally-sync.pending');
    Route::post('{tallySyncQueue}/acknowledge', [App\Http\Controllers\Api\TallySyncApiController::class, 'acknowledge'])->name('api.tally-sync.acknowledge');
});
