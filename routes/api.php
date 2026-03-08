<?php

use App\Http\Controllers\Api\SilverPriceController;
use App\Http\Controllers\Api\AncaratPriceController;
use App\Http\Controllers\Api\DojiPriceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Silver price – Phú Quý (public)
Route::prefix('silver')->group(function () {
    Route::get('current', [SilverPriceController::class, 'currentPrice']);
    Route::get('history', [SilverPriceController::class, 'history']);
    Route::get('percent', [SilverPriceController::class, 'percent']);
});

// Silver price – Ancarat (public)
Route::prefix('ancarat')->group(function () {
    Route::get('current', [AncaratPriceController::class, 'currentPrice']);
    Route::get('history', [AncaratPriceController::class, 'history']);
    Route::get('percent', [AncaratPriceController::class, 'percent']);
});

// Silver price – DOJI (public)
Route::prefix('doji')->group(function () {
    Route::get('current', [DojiPriceController::class, 'currentPrice']);
    Route::get('history', [DojiPriceController::class, 'history']);
    Route::get('percent', [DojiPriceController::class, 'percent']);
});
