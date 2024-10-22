<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WialonController;

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

Route::prefix('wialon')->group(function () {
    Route::post('/login', [WialonController::class, 'login'])->name('wialon.login');
    Route::post('/logout', [WialonController::class, 'logout'])->name('wialon.logout');
    Route::post('/call', [WialonController::class, 'callWialon'])->name('wialon.call');
});
