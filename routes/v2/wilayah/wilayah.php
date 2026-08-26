<?php

use App\Http\Controllers\Api\V2\Wilayah\WilayahController;
use Illuminate\Support\Facades\Route;

Route::get('/kecamatan', [WilayahController::class, 'kecamatan']);
Route::get('/kelurahan', [WilayahController::class, 'kelurahan']);
