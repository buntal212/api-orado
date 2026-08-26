<?php

use App\Http\Controllers\Api\V2\Club\ClubRegistrationController;
use Illuminate\Support\Facades\Route;

Route::post('/pendaftaran', [ClubRegistrationController::class, 'store']);
