<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $login = $credentials['login'];
        $user = User::query()
            ->with(['anggota', 'club'])
            ->where(function ($query) use ($login): void {
                $query->where('email', $login)->orWhere('username', $login);
            })
            ->first();

        $passwordHashes = array_filter([$user?->pass, $user?->password]);
        $passwordValid = false;

        foreach ($passwordHashes as $passwordHash) {
            if (Hash::isHashed($passwordHash) && Hash::check($credentials['password'], $passwordHash)) {
                $passwordValid = true;
                break;
            }
        }

        $isClubAccount = $user?->club !== null;

        if (! $user || ! $passwordValid || $isClubAccount) {
            throw ValidationException::withMessages([
                'login' => ['Email/username atau password tidak sesuai.'],
            ]);
        }

        $deviceName = $credentials['device_name'] ?? 'orado-pengurus';
        $user->tokens()
            ->whereIn('name', [$deviceName, 'orado-pengurus'])
            ->delete();
        $token = $user->createToken($deviceName, ['pengurus'])->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => ['user' => $user, 'token' => $token, 'token_type' => 'Bearer'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->load('anggota')]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
