<?php

namespace App\Http\Controllers\Api\V2\Club;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClubSettingController extends Controller
{
    public function showFee(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->club($request)->only(['biaya_iuran']),
        ]);
    }

    public function updateFee(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'biaya_iuran' => ['required', 'integer', 'min:0', 'max:999999999'],
        ]);
        $club = $this->club($request);
        $club->update($validated);

        return response()->json([
            'message' => 'Biaya iuran berhasil diperbarui.',
            'data' => $club->only(['biaya_iuran']),
        ]);
    }

    private function club(Request $request): Club
    {
        $club = Club::query()->where('user_id', $request->user()->id)->first();

        if (! $club) {
            throw new AuthorizationException('Akun login tidak terhubung dengan data Club.');
        }

        return $club;
    }
}
