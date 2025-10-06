<?php


use Illuminate\Support\Facades\Route;
use Modules\Rates\Http\Controllers\RatesController;

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


Route::prefix('rates')->group(function () {
    Route::get('gift-cards', [RatesController::class, 'giftCards']);
    Route::get('crypto', [RatesController::class, 'crypto']);
});
