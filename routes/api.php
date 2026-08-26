<?php

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
