<?php

use App\Http\Controllers\Api\FcmTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/token', [FcmTokenController::class, 'store']);
    Route::delete('/token', [FcmTokenController::class, 'destroy']);
});
