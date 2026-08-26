<?php

namespace App\Http\Controllers\Api\V2\Wilayah;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WilayahController extends Controller
{
    public function kecamatan(): JsonResponse
    {
        $kecamatan = DB::table('kecamatans')
            ->select(['kode', 'nama_kecamatan'])
            ->orderBy('nama_kecamatan')
            ->get();

        return response()->json([
            'message' => 'Data kecamatan berhasil ditampilkan.',
            'data' => $kecamatan,
        ]);
    }

    public function kelurahan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_kecamatan' => ['required', 'string', 'exists:kecamatans,kode'],
        ]);

        $kelurahan = DB::table('kelurahans')
            ->select(['kode', 'kode_kecamatan', 'nama_kelurahan'])
            ->where('kode_kecamatan', $validated['kode_kecamatan'])
            ->orderBy('nama_kelurahan')
            ->get();

        return response()->json([
            'message' => 'Data kelurahan berhasil ditampilkan.',
            'data' => $kelurahan,
        ]);
    }
}
