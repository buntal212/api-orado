<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterJabatan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $data = MasterJabatan::query()
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->simplePaginate($validated['per_page'] ?? 15);

        return response()->json([
            'message' => 'Data jabatan berhasil ditampilkan.',
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:master_jabatans,nama'],
        ]);

        $jabatan = MasterJabatan::create($validated);

        return response()->json([
            'message' => 'Jabatan berhasil disimpan.',
            'data' => $jabatan,
        ], 201);
    }

    public function update(Request $request, MasterJabatan $masterJabatan): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:master_jabatans,nama,'.$masterJabatan->id],
        ]);

        $masterJabatan->update($validated);

        return response()->json([
            'message' => 'Jabatan berhasil diperbarui.',
            'data' => $masterJabatan->fresh(),
        ]);
    }

    public function destroy(MasterJabatan $masterJabatan): JsonResponse
    {
        $masterJabatan->delete();

        return response()->json(['message' => 'Jabatan berhasil dihapus.']);
    }
}
