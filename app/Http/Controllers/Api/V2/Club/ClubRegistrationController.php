<?php

namespace App\Http\Controllers\Api\V2\Club;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Services\OradoNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClubRegistrationController extends Controller
{
    public function __construct(private readonly OradoNotificationService $notificationService) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_club' => ['required', 'string', 'max:150', 'unique:clubs,nama_club'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'alamat' => ['required', 'string', 'max:1000'],
            'kecamatan' => ['required', 'string', 'exists:kecamatans,kode'],
            'kelurahan' => ['required', 'string', 'exists:kelurahans,kode'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $kecamatan = DB::table('kecamatans')
            ->where('kode', $validated['kecamatan'])
            ->first();

        $kelurahan = DB::table('kelurahans')
            ->where('kode', $validated['kelurahan'])
            ->where('kode_kecamatan', $validated['kecamatan'])
            ->first();

        if (! $kelurahan) {
            throw ValidationException::withMessages([
                'kelurahan' => ['Kelurahan tidak sesuai dengan kecamatan yang dipilih.'],
            ]);
        }

        $club = DB::transaction(function () use ($validated, $kecamatan, $kelurahan): Club {
            $user = User::create([
                'name' => $validated['nama_club'],
                'username' => $validated['username'],
                'email' => 'club.'.strtolower($validated['username']).'@orado.local',
                'password' => $validated['password'],
                'pass' => $validated['password'],
            ]);

            $club = Club::create([
                'user_id' => $user->id,
                'kode_club' => null,
                'nama_club' => $validated['nama_club'],
                'alamat' => $validated['alamat'],
                'kecamatan' => $kecamatan->nama_kecamatan,
                'kelurahan' => $kelurahan->nama_kelurahan,
                'catatan' => $validated['catatan'] ?? null,
                'status' => 'Menunggu verifikasi',
            ]);

            $club->update([
                'kode_club' => $club->id.'-OR-PRB',
            ]);

            return $club;
        });

        $this->notificationService->sendToPengurus(
            'Club Baru Mendaftar',
            $club->nama_club.' telah melakukan pendaftaran.',
            [
                'type' => 'club_registration',
                'club_id' => (string) $club->id,
                'url' => '/club',
            ],
        );

        return response()->json([
            'message' => 'Pendaftaran club berhasil dikirim. Menunggu verifikasi pengurus ORADO.',
            'data' => $club,
        ], 201);
    }
}
