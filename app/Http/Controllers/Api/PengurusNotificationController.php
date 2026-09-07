<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengurusNotification;
use Illuminate\Http\JsonResponse;

class PengurusNotificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Riwayat notifikasi berhasil ditampilkan.',
            'data' => PengurusNotification::query()->latest()->limit(30)->get(),
        ]);
    }
}
