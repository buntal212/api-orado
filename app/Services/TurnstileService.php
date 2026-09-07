<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnstileService
{
    public function verify(string $token, ?string $ip = null): bool
    {
        $secret = config('services.turnstile.secret');
        $verifyUrl = config('services.turnstile.verify_url');

        if (! $secret || ! $verifyUrl) {
            Log::error('Konfigurasi Cloudflare Turnstile belum lengkap.');

            return false;
        }

        try {
            $payload = [
                'secret' => $secret,
                'response' => $token,
            ];

            if ($ip) {
                $payload['remoteip'] = $ip;
            }

            $response = Http::asForm()
                ->timeout(8)
                ->post($verifyUrl, $payload);

            if ($response->json('success') === true) {
                return true;
            }

            Log::warning('Verifikasi Cloudflare Turnstile ditolak.', [
                'error_codes' => $response->json('error-codes', []),
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Koneksi Cloudflare Turnstile gagal.', [
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Verifikasi Cloudflare Turnstile bermasalah.', [
                'message' => $exception->getMessage(),
            ]);
        }

        return false;
    }
}
