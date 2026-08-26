<?php

use App\Http\Controllers\Api\V1\Master\AnggotaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/anggota', [AnggotaController::class, 'index']);
    Route::post('/anggota/simpan', [AnggotaController::class, 'store']);
    Route::post('/anggota/{anggota}/edit', [AnggotaController::class, 'update']);
    Route::post('/anggota/{anggota}/verifikasi', [AnggotaController::class, 'verify']);
    Route::post('/anggota/{anggota}/hapus', [AnggotaController::class, 'destroy']);
});
