<?php

use App\Http\Controllers\GameResourceController;
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

Route::controller(GameResourceController::class)->group(function () {
    Route::prefix('games')->group(function () {
        Route::post('/', 'create');
        Route::get('/{id}', 'getGame');
        Route::delete('/{id}', 'delete');
        Route::post('/{id}/restart', 'restart');
        Route::post('/{id}/{piece}', 'setPiece');
    });
});
