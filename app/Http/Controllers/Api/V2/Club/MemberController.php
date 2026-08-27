<?php

namespace App\Http\Controllers\Api\V2\Club;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Club;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $club = $this->club($request);

        $members = Anggota::query()
            ->where('club_id', $club->id)
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'nik', 'no_hp', 'jenis_kelamin', 'flag']);

        return response()->json([
            'message' => 'Data anggota berhasil ditampilkan.',
            'data' => $members,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'nik' => ['required', 'digits:16'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);
        $club = $this->club($request);

        [$member, $isExisting] = DB::transaction(function () use ($validated, $club): array {
            $member = Anggota::query()->where('nik', $validated['nik'])->lockForUpdate()->first();

            if ($member) {
                $member->update(['club_id' => $club->id]);

                return [$member->fresh(), true];
            }

            $member = Anggota::create([
                'club_id' => $club->id,
                'name' => $validated['name'],
                'nama' => $validated['name'],
                'email' => ($validated['email'] ?? null) ?: 'anggota-'.$validated['nik'].'@orado.local',
                'nik' => $validated['nik'],
                'no_hp' => $validated['no_hp'] ?? null,
                'kelompok_jabatan' => '2',
                'flag' => 1,
            ]);

            return [$member, false];
        });

        return response()->json([
            'message' => $isExisting
                ? 'NIK sudah terdaftar di ORADO dan berhasil ditambahkan ke club.'
                : 'Anggota baru berhasil disimpan.',
            'data' => $member->only(['id', 'name', 'email', 'nik', 'no_hp', 'jenis_kelamin', 'flag']),
        ], $isExisting ? 200 : 201);
    }

    private function club(Request $request): Club
    {
        $club = Club::query()->where('user_id', $request->user()->id)->first();

        if (! $club) {
            throw new AuthorizationException('Akun login tidak terhubung dengan data club. Silakan login menggunakan akun club.');
        }

        return $club;
    }
}
