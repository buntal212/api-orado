<?php

namespace App\Http\Controllers\Api\V2\Club;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Club;
use App\Models\Iuran;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberFeeController extends Controller
{
    public function paidMembers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $club = $this->club($request);

        $members = Anggota::query()
            ->whereHas('iurans', fn (Builder $query): Builder => $query->where('club_id', $club->id))
            ->when($validated['search'] ?? null, fn (Builder $query, string $search): Builder => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'nik']);

        return response()->json(['data' => $members]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'month' => ['nullable', 'date_format:Y-m'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);
        $club = $this->club($request);
        $fees = Iuran::query()
            ->with('anggota:id,name,nik')
            ->where('club_id', $club->id)
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('anggota', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"));
            })
            ->when($validated['month'] ?? null, fn (Builder $query, string $month): Builder => $query->whereBetween('periode', ["{$month}-01", "{$month}-31"]))
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->paginate($validated['per_page'] ?? 15);

        return response()->json([
            'data' => collect($fees->items())->map(fn (Iuran $fee): array => $this->feeData($fee))->values(),
            'meta' => [
                'biaya_iuran' => $club->biaya_iuran,
                'current_page' => $fees->currentPage(),
                'last_page' => $fees->lastPage(),
                'per_page' => $fees->perPage(),
                'total' => $fees->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'anggota_id' => ['required', 'integer', 'exists:anggotas,id'],
            'periode' => ['required', 'date_format:Y-m'],
            'tanggal_bayar' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);
        $club = $this->club($request);

        $fee = DB::transaction(function () use ($validated, $club): Iuran {
            $member = Anggota::query()->where('id', $validated['anggota_id'])->where('club_id', $club->id)->where('flag', 2)->lockForUpdate()->first();
            if (! $member) {
                throw ValidationException::withMessages(['anggota_id' => ['Anggota harus aktif dan terdaftar pada Club Anda.']]);
            }

            $period = $validated['periode'].'-01';
            if (Iuran::query()->where('club_id', $club->id)->where('anggota_id', $member->id)->whereDate('periode', $period)->exists()) {
                throw ValidationException::withMessages(['periode' => ['Iuran anggota ini untuk periode tersebut sudah tercatat.']]);
            }

            return Iuran::create([
                'club_id' => $club->id,
                'anggota_id' => $member->id,
                'periode' => $period,
                'nominal' => $club->biaya_iuran,
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'catatan' => $validated['catatan'] ?? null,
            ])->load('anggota:id,name,nik');
        });

        return response()->json(['message' => 'Iuran anggota berhasil dicatat.', 'data' => $this->feeData($fee)], 201);
    }

    private function club(Request $request): Club
    {
        $club = Club::query()->where('user_id', $request->user()->id)->first();
        if (! $club) {
            throw new AuthorizationException('Akun login tidak terhubung dengan data Club.');
        }

        return $club;
    }

    private function feeData(Iuran $fee): array
    {
        return ['id' => $fee->id, 'anggota_id' => $fee->anggota_id, 'anggota' => ['id' => $fee->anggota->id, 'name' => $fee->anggota->name, 'nik' => $fee->anggota->nik], 'periode' => $fee->periode->format('Y-m-d'), 'nominal' => $fee->nominal, 'tanggal_bayar' => $fee->tanggal_bayar->format('Y-m-d'), 'catatan' => $fee->catatan];
    }
}
