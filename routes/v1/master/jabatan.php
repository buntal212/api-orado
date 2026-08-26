<?php

use App\Http\Controllers\Api\V1\Master\JabatanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/jabatan', [JabatanController::class, 'index']);
    Route::post('/jabatan/simpan', [JabatanController::class, 'store']);
    Route::post('/jabatan/{masterJabatan}/edit', [JabatanController::class, 'update']);
    Route::post('/jabatan/{masterJabatan}/hapus', [JabatanController::class, 'destroy']);
});
