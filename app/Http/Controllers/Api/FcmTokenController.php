<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FcmTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'app_type' => ['required', 'string', Rule::in(['pengurus', 'club'])],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        Log::info('Permintaan penyimpanan token FCM diterima.', [
            'user_id' => $user->id,
            'user_type' => $user::class,
            'app_type' => $validated['app_type'],
            'device_name' => $validated['device_name'] ?? null,
            'database' => DB::connection()->getDatabaseName(),
        ]);

        $fcmToken = FcmToken::query()->updateOrCreate(
            ['token_hash' => hash('sha256', $validated['token'])],
            [
                'user_id' => $user->id,
                'user_type' => $user::class,
                'app_type' => $validated['app_type'],
                'token' => $validated['token'],
                'device_name' => $validated['device_name'] ?? null,
                'last_used_at' => now(),
            ],
        );

        Log::info('Token FCM berhasil disimpan.', [
            'fcm_token_id' => $fcmToken->id,
            'user_id' => $fcmToken->user_id,
            'app_type' => $fcmToken->app_type,
            'device_name' => $fcmToken->device_name,
            'database' => DB::connection()->getDatabaseName(),
        ]);

        return response()->json([
            'message' => 'Token notifikasi berhasil disimpan.',
            'data' => $fcmToken,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $user = $request->user();
        FcmToken::query()
            ->where('token_hash', hash('sha256', $validated['token']))
            ->where('user_id', $user->id)
            ->where('user_type', $user::class)
            ->delete();

        return response()->json(['message' => 'Token notifikasi berhasil dihapus.']);
    }
}
