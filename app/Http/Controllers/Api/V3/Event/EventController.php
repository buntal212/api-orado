<?php

namespace App\Http\Controllers\Api\V3\Event;

use App\Http\Controllers\Controller;
use App\Models\MasterEvent;
use App\Models\PendaftaranEventHeader;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'search' => ['nullable', 'string', 'max:100'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ],
            $this->validationMessages(),
            $this->validationAttributes(),
        );

        $events = MasterEvent::query()
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('kode_event', 'like', "%{$search}%")
                        ->orWhere('nama_event', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%");
                });
            })
            ->latest('tanggal_mulai')
            ->simplePaginate($validated['per_page'] ?? 15);

        return response()->json([
            'message' => 'Data event berhasil ditampilkan.',
            'data' => $events,
        ]);
    }

    public function participants(Request $request): JsonResponse
    {
        $validated = $this->validatedParticipantFilter($request);

        $participants = $this->participantQuery($validated)
            ->latest()
            ->simplePaginate(15);

        return response()->json([
            'message' => 'Data peserta event berhasil ditampilkan.',
            'data' => $participants,
        ]);
    }

    public function printParticipants(Request $request): JsonResponse
    {
        $validated = $this->validatedParticipantFilter($request);
        $eventId = $validated['master_event_id'] ?? null;

        return response()->json([
            'message' => 'Data peserta event untuk cetak berhasil ditampilkan.',
            'data' => $this->participantQuery($validated)->latest()->get(),
            'meta' => [
                'event' => $eventId
                    ? MasterEvent::query()->find($eventId, ['id', 'kode_event', 'nama_event'])
                    : null,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedEvent($request);
        $validated['kode_event'] = $this->generateKodeEvent($validated['tanggal_mulai']);
        $event = MasterEvent::create($validated);

        return response()->json([
            'message' => 'Event berhasil disimpan.',
            'data' => $event,
        ], 201);
    }

    public function update(Request $request, MasterEvent $masterEvent): JsonResponse
    {
        $masterEvent->update($this->validatedEvent($request));

        return response()->json([
            'message' => 'Event berhasil diperbarui.',
            'data' => $masterEvent->fresh(),
        ]);
    }

    public function destroy(MasterEvent $masterEvent): JsonResponse
    {
        $masterEvent->delete();

        return response()->json(['message' => 'Event berhasil dihapus.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedEvent(Request $request): array
    {
        return $request->validate(
            [
                'nama_event' => ['required', 'string', 'max:150'],
                'deskripsi' => ['nullable', 'string'],
                'lokasi' => ['nullable', 'string', 'max:255'],
                'tanggal_mulai' => ['required', 'date'],
                'tanggal_selesai' => ['required', 'date'],
                'pendaftaran_mulai' => ['nullable', 'date'],
                'pendaftaran_selesai' => ['nullable', 'date'],
                'kuota_peserta' => ['nullable', 'integer', 'min:1'],
                'biaya_pendaftaran' => ['required', 'integer', 'min:0'],
                'poster' => ['nullable', 'string', 'max:255'],
                'status' => ['required', 'in:draft,dibuka,ditutup'],
            ],
            $this->validationMessages(),
            $this->validationAttributes(),
        );
    }

    /** @param array<string, mixed> $validated */
    private function participantQuery(array $validated): Builder
    {
        return PendaftaranEventHeader::query()
            ->with([
                'event:id,kode_event,nama_event',
                'rincis:id,pendaftaran_event_header_id,nik_atlet_satu,nama_atlet_satu,tanggal_lahir_atlet_satu,jenis_kelamin_atlet_satu,no_hp_atlet_satu,nik_atlet_dua,nama_atlet_dua,tanggal_lahir_atlet_dua,jenis_kelamin_atlet_dua,no_hp_atlet_dua',
            ])
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('kode_pendaftaran', 'like', "%{$search}%")
                        ->orWhere('nama_tim', 'like', "%{$search}%")
                        ->orWhereHas('event', fn (Builder $eventQuery) => $eventQuery
                            ->where('kode_event', 'like', "%{$search}%")
                            ->orWhere('nama_event', 'like', "%{$search}%"));
                });
            })
            ->when(
                $validated['master_event_id'] ?? null,
                fn (Builder $query, int $eventId) => $query->where('master_event_id', $eventId),
            );
    }

    /** @return array<string, mixed> */
    private function validatedParticipantFilter(Request $request): array
    {
        return $request->validate(
            [
                'search' => ['nullable', 'string', 'max:100'],
                'master_event_id' => ['nullable', 'integer', 'exists:master_events,id'],
            ],
            $this->validationMessages(),
            $this->validationAttributes(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka bulat.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'max.string' => ':attribute tidak boleh lebih dari :max karakter.',
            'max.integer' => ':attribute tidak boleh lebih dari :max.',
            'min.integer' => ':attribute minimal :min.',
            'in' => 'Pilihan :attribute tidak valid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'search' => 'kata pencarian',
            'per_page' => 'jumlah data per halaman',
            'nama_event' => 'nama event',
            'deskripsi' => 'deskripsi',
            'lokasi' => 'lokasi',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_selesai' => 'tanggal selesai',
            'pendaftaran_mulai' => 'pendaftaran mulai',
            'pendaftaran_selesai' => 'pendaftaran selesai',
            'kuota_peserta' => 'kuota peserta',
            'biaya_pendaftaran' => 'biaya pendaftaran',
            'poster' => 'URL poster',
            'status' => 'status',
        ];
    }

    private function generateKodeEvent(string $tanggalMulai): string
    {
        $nomorUrut = MasterEvent::count() + 1;
        $periodeEvent = Carbon::parse($tanggalMulai);

        return sprintf('%05d-%s', $nomorUrut, $periodeEvent->format('m-Y'));
    }
}
