<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\PengurusNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class OradoNotificationService
{
    public function __construct(private readonly FirebaseMessagingService $firebaseMessaging) {}

    public function sendToPengurus(string $title, string $body, array $data = []): void
    {
        $notification = PengurusNotification::create([
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
        $data['notification_id'] = (string) $notification->id;
        $tokens = FcmToken::query()->where('app_type', 'pengurus')->get();

        $this->sendToTokens($tokens, $title, $body, $data, 'pengurus', dataOnly: true);
    }

    public function sendToClubUser(int $userId, string $title, string $body, array $data = []): void
    {
        $tokens = FcmToken::query()
            ->where('app_type', 'club')
            ->where('user_id', $userId)
            ->where('user_type', User::class)
            ->get();

        $this->sendToTokens($tokens, $title, $body, $data, 'club', $userId);
    }

    /**
     * @param  Collection<int, string>  $tokens
     */
    private function sendToTokens(
        Collection $tokens,
        string $title,
        string $body,
        array $data,
        string $appType,
        ?int $userId = null,
        bool $dataOnly = false,
    ): void {
        if ($tokens->isEmpty()) {
            Log::info('Tidak ada token FCM tujuan.', [
                'event_type' => $data['type'] ?? null,
                'app_type' => $appType,
                'user_id' => $userId,
                'device_count' => 0,
            ]);

            return;
        }

        try {
            $results = $this->firebaseMessaging->sendToTokens($tokens, $title, $body, $data, $dataOnly);
            $successCount = count(array_filter($results));
            $failedDevices = $tokens
                ->filter(fn (FcmToken $token): bool => ! ($results[(string) $token->id] ?? false))
                ->map(fn (FcmToken $token): array => [
                    'id' => $token->id,
                    'device_name' => $token->device_name,
                ])
                ->values()
                ->all();

            Log::info('Pengiriman notifikasi ORADO selesai.', [
                'event_type' => $data['type'] ?? null,
                'app_type' => $appType,
                'user_id' => $userId,
                'device_count' => $tokens->count(),
                'success_count' => $successCount,
                'failed_count' => count($results) - $successCount,
                'failed_devices' => $failedDevices,
            ]);
        } catch (Throwable $exception) {
            Log::error('Pengiriman notifikasi ORADO gagal.', [
                'event_type' => $data['type'] ?? null,
                'app_type' => $appType,
                'user_id' => $userId,
                'device_count' => $tokens->count(),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
