<?php

use App\Http\Controllers\Api\V1\Master\ClubController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/club', [ClubController::class, 'index']);
    Route::post('/club/{club}/verifikasi', [ClubController::class, 'verify']);
    Route::post('/club/{club}/tolak', [ClubController::class, 'reject']);
});
