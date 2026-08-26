<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\MemberVerificationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/pendaftaran-anggota', [MemberVerificationController::class, 'register']);
Route::get('/verifikasi-anggota', [MemberVerificationController::class, 'find']);
Route::post('/verifikasi-anggota/simpan', [MemberVerificationController::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
