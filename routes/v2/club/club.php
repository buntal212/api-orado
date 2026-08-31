<?php

use App\Http\Controllers\Api\V2\Club\ClubRegistrationController;
use App\Http\Controllers\Api\V2\Club\ClubSettingController;
use App\Http\Controllers\Api\V2\Club\MemberController;
use App\Http\Controllers\Api\V2\Club\MemberFeeController;
use Illuminate\Support\Facades\Route;

Route::post('/pendaftaran', [ClubRegistrationController::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/anggota', [MemberController::class, 'index']);
    Route::post('/anggota', [MemberController::class, 'store']);
    Route::get('/iuran', [MemberFeeController::class, 'index']);
    Route::get('/iuran/anggota', [MemberFeeController::class, 'paidMembers']);
    Route::post('/iuran', [MemberFeeController::class, 'store']);
    Route::get('/pengaturan/biaya-iuran', [ClubSettingController::class, 'showFee']);
    Route::put('/pengaturan/biaya-iuran', [ClubSettingController::class, 'updateFee']);
});
