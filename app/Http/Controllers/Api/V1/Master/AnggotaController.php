<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnggotaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'in:terverifikasi,menunggu'],
        ]);

        $data = Anggota::query()
            ->with('user:id,username,email')
            ->when(($validated['status'] ?? 'terverifikasi') === 'terverifikasi', function (Builder $query): void {
                $query->where('flag', 2);
            })
            ->when(($validated['status'] ?? null) === 'menunggu', function (Builder $query): void {
                $query->where('flag', '!=', 2);
            })
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('username', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->simplePaginate($validated['per_page'] ?? 15)
            ->through(fn (Anggota $anggota): array => $this->memberData($anggota));

        return response()->json([
            'message' => 'Data anggota berhasil ditampilkan.',
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateData($request);
        $validated['kelompok_jabatan'] = '1';

        $anggota = Anggota::create($validated);

        return response()->json([
            'message' => 'Data anggota berhasil disimpan.',
            'data' => $this->memberData($anggota),
        ], 201);
    }

    public function update(Request $request, Anggota $anggota): JsonResponse
    {
        $validated = $this->validateData($request, $anggota);
        $validated['kelompok_jabatan'] = '1';
        $anggota->update($validated);

        return response()->json([
            'message' => 'Data anggota berhasil diperbarui.',
            'data' => $this->memberData($anggota->fresh()->load('user')),
        ]);
    }

    public function destroy(Anggota $anggota): JsonResponse
    {
        $anggota->delete();

        return response()->json(['message' => 'Data anggota berhasil dihapus.']);
    }

    public function verify(Request $request, Anggota $anggota): JsonResponse
    {
        $validated = $request->validate([
            'jabatan' => [
                Rule::requiredIf((string) $anggota->kelompok_jabatan === '1'),
                'nullable',
                'string',
                'max:100',
                'exists:master_jabatans,nama',
            ],
        ]);

        $anggota->update([
            'jabatan' => (string) $anggota->kelompok_jabatan === '1'
                ? $validated['jabatan']
                : $anggota->jabatan,
            'flag' => 2,
        ]);

        return response()->json([
            'message' => 'Data anggota berhasil diverifikasi.',
            'data' => $this->memberData($anggota->fresh()->load('user')),
        ]);
    }

    /**
     * @return array{name: string, nik: string, no_hp: ?string, jabatan: ?string}
     */
    private function validateData(Request $request, ?Anggota $anggota = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nik' => ['required', 'digits:16', 'unique:anggotas,nik,'.$anggota?->id],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['nullable', 'string', 'max:100', 'exists:master_jabatans,nama'],
        ]);
    }

    /**
     * @return array{id: int, name: string, nik: string, no_hp: ?string, username: ?string, email: ?string, kelompok_jabatan: ?string, jabatan: ?string, flag: int}
     */
    private function memberData(Anggota $anggota): array
    {
        return [
            'id' => $anggota->id,
            'name' => $anggota->name,
            'nik' => $anggota->nik,
            'no_hp' => $anggota->no_hp,
            'username' => $anggota->user?->username,
            'email' => $anggota->user?->email,
            'kelompok_jabatan' => $anggota->kelompok_jabatan,
            'jabatan' => $anggota->jabatan,
            'flag' => $anggota->flag,
        ];
    }
}
