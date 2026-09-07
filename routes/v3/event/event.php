<?php

use App\Http\Controllers\Api\V3\Event\EventController;
use App\Http\Controllers\Api\V3\Event\EventRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/tersedia', [EventRegistrationController::class, 'events']);
Route::post('/pendaftaran', [EventRegistrationController::class, 'store']);
Route::get('/pendaftaran/{kodePendaftaran}', [EventRegistrationController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/', [EventController::class, 'index']);
    Route::post('/simpan', [EventController::class, 'store']);
    Route::post('/{masterEvent}/edit', [EventController::class, 'update']);
    Route::post('/{masterEvent}/hapus', [EventController::class, 'destroy']);
});
