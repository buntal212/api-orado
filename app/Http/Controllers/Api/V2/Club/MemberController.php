<?php

namespace App\Http\Controllers\Api\V2\Club;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Club;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'age_group' => ['nullable', 'in:junior,senior,professional'],
            'gender' => ['nullable', 'in:L,P'],
        ]);
        $club = $this->club($request);

        $members = Anggota::query()
            ->where('club_id', $club->id)
            ->where('flag', 2)
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->when($validated['age_group'] ?? null, function (Builder $query, string $ageGroup): void {
                $today = now()->startOfDay();

                match ($ageGroup) {
                    'junior' => $query->whereDate('tanggal_lahir', '>', $today->copy()->subYears(17)),
                    'senior' => $query
                        ->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(17))
                        ->whereDate('tanggal_lahir', '>', $today->copy()->subYears(24)),
                    'professional' => $query->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(24)),
                };
            })
            ->when($validated['gender'] ?? null, fn (Builder $query, string $gender): Builder => $query->where('jenis_kelamin', $gender))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'nik', 'tanggal_lahir', 'no_hp', 'jenis_kelamin', 'flag']);

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
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);
        $club = $this->club($request);

        [$member, $isExisting] = DB::transaction(function () use ($validated, $club): array {
            $member = Anggota::query()->where('nik', $validated['nik'])->lockForUpdate()->first();

            if ($member) {
                if ($member->club_id) {
                    throw ValidationException::withMessages([
                        'nik' => ['NIK ini sudah terdaftar pada Club dan tidak dapat ditambahkan kembali.'],
                    ]);
                }

                $member->update(['club_id' => $club->id]);

                return [$member->fresh(), true];
            }

            $member = Anggota::create([
                'club_id' => $club->id,
                'name' => $validated['name'],
                'nama' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'nik' => $validated['nik'],
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'no_hp' => $validated['no_hp'] ?? null,
                'kelompok_jabatan' => '2',
                'flag' => 1,
            ]);

            if (! $member->email) {
                $member->update(['email' => "a-{$member->id}@o.id"]);
            }

            return [$member->fresh(), false];
        });

        return response()->json([
            'message' => $isExisting
                ? 'NIK terdaftar di ORADO dan berhasil ditambahkan ke Club.'
                : 'Anggota baru berhasil disimpan.',
            'data' => $member->only(['id', 'name', 'email', 'nik', 'tanggal_lahir', 'no_hp', 'jenis_kelamin', 'flag']),
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
