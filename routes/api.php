<?php

use App\Http\Controllers\Api\PengurusNotificationController;
use App\Http\Controllers\Api\PengumumanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')
    ->group(base_path('routes/v1/auth/auth.php'));

Route::prefix('v1/master')
    ->group(base_path('routes/v1/master/jabatan.php'));

Route::prefix('v1/master')
    ->group(base_path('routes/v1/master/anggota.php'));

Route::prefix('v1/master')
    ->group(base_path('routes/v1/master/club.php'));

Route::prefix('v2/auth')
    ->group(base_path('routes/v2/auth/auth.php'));

Route::prefix('v2/club')
    ->group(base_path('routes/v2/club/club.php'));

Route::prefix('v2/wilayah')
    ->group(base_path('routes/v2/wilayah/wilayah.php'));

Route::prefix('v3/event')
    ->group(base_path('routes/v3/event/event.php'));

Route::prefix('fcm')
    ->group(base_path('routes/fcm/fcm.php'));

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/notifikasi', [PengurusNotificationController::class, 'index']);
    Route::post('/notifikasi/tandai-semua-dibaca', [PengurusNotificationController::class, 'markAllAsRead']);
    Route::post('/notifikasi/{notification}/tandai-dibaca', [PengurusNotificationController::class, 'markAsRead']);
    Route::get('/pengumuman/penerima', [PengumumanController::class, 'recipients']);
    Route::post('/pengumuman', [PengumumanController::class, 'store']);
});
