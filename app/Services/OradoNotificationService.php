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
        $pengurusIds = User::query()->whereDoesntHave('club')->pluck('id')->all();

        $this->sendToPengurusUsers($pengurusIds, $title, $body, $data);
    }

    /** @param array<int, int> $userIds */
    public function sendToPengurusUsers(array $userIds, string $title, string $body, array $data = []): void
    {
        if ($userIds === []) {
            return;
        }

        $tokensByUser = FcmToken::query()
            ->where('app_type', 'pengurus')
            ->whereNotNull('user_id')
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $pengurus = User::query()->whereIn('id', $userIds)->get(['id']);

        foreach ($pengurus as $pengurusUser) {
            $notification = PengurusNotification::create([
                'user_id' => $pengurusUser->id,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);
            $notificationData = [...$data, 'notification_id' => (string) $notification->id];

            $this->sendToTokens(
                $tokensByUser->get($pengurusUser->id, collect()),
                $title,
                $body,
                $notificationData,
                'pengurus',
                $pengurusUser->id,
            );
        }
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
     * @param  Collection<int, FcmToken>  $tokens
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
