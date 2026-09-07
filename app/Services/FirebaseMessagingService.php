<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class FirebaseMessagingService
{
    private ?string $accessToken = null;

    private int $accessTokenExpiresAt = 0;

    public function sendToToken(string $token, string $title, string $body, array $data = [], bool $dataOnly = false): bool
    {
        try {
            $message = [
                'token' => $token,
                'data' => $this->normalizeData([
                    ...$data,
                    'title' => $title,
                    'body' => $body,
                ]),
            ];

            if (! $dataOnly) {
                $message['notification'] = ['title' => $title, 'body' => $body];
            }

            $response = Http::withToken($this->accessToken())
                ->acceptJson()
                ->post($this->messageEndpoint(), [
                    'message' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            if ($this->isInvalidTokenResponse($response->json())) {
                FcmToken::query()->where('token_hash', hash('sha256', $token))->delete();
            }

            Log::warning('Pengiriman FCM gagal.', ['status' => $response->status(), 'response' => $response->json()]);
        } catch (ConnectionException|RuntimeException $exception) {
            Log::error('Koneksi Firebase Messaging gagal.', ['message' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Pengiriman Firebase Messaging gagal.', ['message' => $exception->getMessage()]);
        }

        return false;
    }

    public function sendToTokens(iterable $tokens, string $title, string $body, array $data = [], bool $dataOnly = false): array
    {
        $results = [];
        foreach ($tokens as $token) {
            $value = $token instanceof FcmToken ? $token->token : (string) $token;
            $key = $token instanceof FcmToken ? (string) $token->id : hash('sha256', $value);
            $results[$key] = $this->sendToToken($value, $title, $body, $data, $dataOnly);
        }

        return $results;
    }

    private function accessToken(): string
    {
        if ($this->accessToken !== null && $this->accessTokenExpiresAt > now()->addMinute()->timestamp) {
            return $this->accessToken;
        }

        $projectId = config('services.firebase.project_id');
        $clientEmail = config('services.firebase.client_email');
        $privateKey = str_replace('\\n', "\n", (string) config('services.firebase.private_key'));
        if (! $projectId || ! $clientEmail || ! $privateKey) {
            throw new RuntimeException('Konfigurasi Firebase service account belum lengkap.');
        }

        $now = now()->timestamp;
        $jwt = $this->jwt([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], $privateKey);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ])->throw();

        $this->accessToken = $response->json('access_token');
        if (! $this->accessToken) {
            throw new RuntimeException('OAuth2 access token Firebase tidak tersedia.');
        }

        $this->accessTokenExpiresAt = $now + (int) $response->json('expires_in', 3600);

        return $this->accessToken;
    }

    private function messageEndpoint(): string
    {
        return 'https://fcm.googleapis.com/v1/projects/'.config('services.firebase.project_id').'/messages:send';
    }

    private function jwt(array $claims, string $privateKey): string
    {
        $segments = [$this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR)), $this->base64Url(json_encode($claims, JSON_THROW_ON_ERROR))];
        $payload = implode('.', $segments);
        if (! openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('JWT Firebase tidak dapat ditandatangani.');
        }

        return $payload.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function isInvalidTokenResponse(?array $response): bool
    {
        $status = data_get($response, 'error.status');

        return $status === 'UNREGISTERED';
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[(string) $key] = match (true) {
                is_string($value) => $value,
                is_int($value), is_float($value) => (string) $value,
                is_bool($value) => $value ? '1' : '0',
                $value === null => '',
                is_array($value), is_object($value) => json_encode($value, JSON_THROW_ON_ERROR),
                default => (string) $value,
            };
        }

        return $normalized;
    }
}
