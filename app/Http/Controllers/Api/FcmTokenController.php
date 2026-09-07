<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Services\FirebaseMessagingService;
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

        if (! empty($validated['device_name'])) {
            FcmToken::query()
                ->where('user_id', $user->id)
                ->where('user_type', $user::class)
                ->where('app_type', $validated['app_type'])
                ->where('device_name', $validated['device_name'])
                ->where('token_hash', '!=', hash('sha256', $validated['token']))
                ->delete();
        }

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

    public function test(Request $request, FirebaseMessagingService $firebaseMessaging): JsonResponse
    {
        $user = $request->user();
        $tokens = FcmToken::query()
            ->where('user_id', $user->id)
            ->where('user_type', $user::class)
            ->where('app_type', 'pengurus')
            ->get();

        if ($tokens->isEmpty()) {
            return response()->json([
                'message' => 'Token notifikasi perangkat ini belum tersedia. Aktifkan notifikasi terlebih dahulu.',
            ], 422);
        }

        $results = $firebaseMessaging->sendToTokens(
            $tokens,
            'Uji Notifikasi ORADO',
            'Notifikasi percobaan berhasil dikirim ke perangkat Anda.',
            [
                'type' => 'notification_test',
                'url' => '/notifikasi',
            ],
            dataOnly: true,
        );
        $successCount = count(array_filter($results));

        Log::info('Pengujian notifikasi FCM selesai.', [
            'user_id' => $user->id,
            'device_count' => $tokens->count(),
            'success_count' => $successCount,
            'failed_count' => count($results) - $successCount,
        ]);

        if ($successCount === 0) {
            return response()->json([
                'message' => 'Firebase menolak token perangkat. Aktifkan ulang notifikasi lalu coba kembali.',
            ], 422);
        }

        return response()->json([
            'message' => 'Notifikasi uji berhasil dikirim.',
            'data' => ['success_count' => $successCount],
        ]);
    }
}
