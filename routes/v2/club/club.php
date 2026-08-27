<?php

use App\Http\Controllers\Api\V2\Club\ClubRegistrationController;
use App\Http\Controllers\Api\V2\Club\MemberController;
use Illuminate\Support\Facades\Route;

Route::post('/pendaftaran', [ClubRegistrationController::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/anggota', [MemberController::class, 'index']);
    Route::post('/anggota', [MemberController::class, 'store']);
});
