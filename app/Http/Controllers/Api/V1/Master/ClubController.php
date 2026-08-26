<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClubController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'in:terverifikasi,menunggu'],
        ]);

        $data = Club::query()
            ->with('user:id,username')
            ->when(($validated['status'] ?? 'terverifikasi') === 'terverifikasi', function (Builder $query): void {
                $query->where('status', 'Terverifikasi');
            })
            ->when(($validated['status'] ?? null) === 'menunggu', function (Builder $query): void {
                $query->where('status', 'Menunggu verifikasi');
            })
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nama_club', 'like', "%{$search}%")
                        ->orWhere('kode_club', 'like', "%{$search}%")
                        ->orWhere('kecamatan', 'like', "%{$search}%")
                        ->orWhere('kelurahan', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->simplePaginate($validated['per_page'] ?? 15)
            ->through(fn (Club $club): array => $this->clubData($club));

        return response()->json([
            'message' => 'Data club berhasil ditampilkan.',
            'data' => $data,
        ]);
    }

    public function verify(Club $club): JsonResponse
    {
        $this->ensurePendingVerification($club);
        $club->update(['status' => 'Terverifikasi']);

        return response()->json([
            'message' => 'Club berhasil diverifikasi.',
            'data' => $this->clubData($club->fresh()->load('user')),
        ]);
    }

    public function reject(Club $club): JsonResponse
    {
        $this->ensurePendingVerification($club);
        $club->update(['status' => 'Ditolak']);

        return response()->json([
            'message' => 'Verifikasi club berhasil ditolak.',
            'data' => $this->clubData($club->fresh()->load('user')),
        ]);
    }

    /**
     * @return array{id: int, kode_club: ?string, nama_club: string, alamat: ?string, kelurahan: ?string, kecamatan: ?string, status: ?string, catatan: ?string, username: ?string}
     */
    private function clubData(Club $club): array
    {
        return [
            'id' => $club->id,
            'kode_club' => $club->kode_club,
            'nama_club' => $club->nama_club,
            'alamat' => $club->alamat,
            'kelurahan' => $club->kelurahan,
            'kecamatan' => $club->kecamatan,
            'status' => $club->status,
            'catatan' => $club->catatan,
            'username' => $club->user?->username,
        ];
    }

    private function ensurePendingVerification(Club $club): void
    {
        if ($club->status !== 'Menunggu verifikasi') {
            throw ValidationException::withMessages([
                'status' => ['Club ini sudah diproses dan tidak dapat diverifikasi kembali.'],
            ]);
        }
    }
}
