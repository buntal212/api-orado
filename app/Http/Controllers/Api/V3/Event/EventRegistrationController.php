<?php

namespace App\Http\Controllers\Api\V3\Event;

use App\Http\Controllers\Controller;
use App\Models\MasterEvent;
use App\Models\PendaftaranEventHeader;
use App\Models\PendaftaranEventRinci;
use App\Services\OradoNotificationService;
use App\Services\TurnstileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventRegistrationController extends Controller
{
    public function __construct(
        private readonly OradoNotificationService $notificationService,
        private readonly TurnstileService $turnstile,
    ) {}

    public function events(): JsonResponse
    {
        $today = today()->toDateString();

        $events = MasterEvent::query()
            ->where('status', 'dibuka')
            ->where(function ($query) use ($today): void {
                $query->whereNull('pendaftaran_mulai')
                    ->orWhereDate('pendaftaran_mulai', '<=', $today);
            })
            ->where(function ($query) use ($today): void {
                $query->whereNull('pendaftaran_selesai')
                    ->orWhereDate('pendaftaran_selesai', '>=', $today);
            })
            ->orderBy('tanggal_mulai')
            ->get();

        return response()->json([
            'message' => 'Daftar event tersedia berhasil ditampilkan.',
            'data' => $events,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        if (! $this->turnstile->verify($validated['turnstile_token'], $request->ip())) {
            throw ValidationException::withMessages([
                'turnstile_token' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
            ]);
        }

        $event = MasterEvent::findOrFail($validated['master_event_id']);

        if (! $this->pendaftaranAktif($event)) {
            return response()->json(['message' => 'Pendaftaran untuk event ini tidak aktif atau sudah berakhir.'], 422);
        }

        $this->pastikanAtletBelumTerdaftar($event, $validated);

        $pendaftaran = DB::transaction(function () use ($validated, $event): PendaftaranEventHeader {
            $biayaPeserta = $event->biaya_pendaftaran;

            $header = PendaftaranEventHeader::create([
                'master_event_id' => $event->id,
                'kode_event' => $event->kode_event,
                'kode_pendaftaran' => sprintf('REG-%05d', PendaftaranEventHeader::count() + 1),
                'nama_tim' => $validated['nama_tim'],
                'nama_pendaftar' => $validated['nama_pendaftar'] ?? $validated['nama_tim'],
                'no_hp' => $validated['no_hp'] ?? $validated['no_hp_atlet_satu'],
                'email' => $validated['email'] ?? null,
                'jumlah_peserta' => 2,
                'total_biaya' => $biayaPeserta,
                'status_pendaftaran' => 'terdaftar',
                'status_pembayaran' => 'belum_bayar',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $header->rincis()->create([
                'nama_peserta' => $validated['nama_atlet_satu'],
                'nik_atlet_satu' => $validated['nik_atlet_satu'],
                'nama_atlet_satu' => $validated['nama_atlet_satu'],
                'tanggal_lahir_atlet_satu' => $validated['tanggal_lahir_atlet_satu'],
                'jenis_kelamin_atlet_satu' => $validated['jenis_kelamin_atlet_satu'],
                'no_hp_atlet_satu' => $validated['no_hp_atlet_satu'],
                'nik_atlet_dua' => $validated['nik_atlet_dua'],
                'nama_atlet_dua' => $validated['nama_atlet_dua'],
                'tanggal_lahir_atlet_dua' => $validated['tanggal_lahir_atlet_dua'],
                'jenis_kelamin_atlet_dua' => $validated['jenis_kelamin_atlet_dua'],
                'no_hp_atlet_dua' => $validated['no_hp_atlet_dua'],
                'biaya_pendaftaran' => $biayaPeserta,
                'status' => 'terdaftar',
            ]);

            return $header;
        });

        $this->notificationService->sendToPengurus(
            'Pendaftaran Event Baru',
            $pendaftaran->nama_tim.' mendaftar pada '.$event->nama_event.'.',
            [
                'type' => 'event_registration',
                'menu' => 'event-peserta',
                'menu_label' => 'Data Peserta Event',
                'event_id' => (string) $event->id,
                'registration_code' => $pendaftaran->kode_pendaftaran,
                'url' => '/event-peserta',
            ],
        );

        return response()->json([
            'message' => 'Pendaftaran event berhasil disimpan.',
            'data' => $pendaftaran->load(['event', 'rincis']),
        ], 201);
    }

    public function show(string $kodePendaftaran): JsonResponse
    {
        $pendaftaran = PendaftaranEventHeader::query()
            ->with(['event', 'rincis'])
            ->where('kode_pendaftaran', $kodePendaftaran)
            ->first();

        if (! $pendaftaran) {
            return response()->json(['message' => 'Bukti pendaftaran tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Bukti pendaftaran berhasil ditampilkan.',
            'data' => $pendaftaran,
        ]);
    }

    /** @return array<string, array<int, string>> */
    private function rules(): array
    {
        return [
            'master_event_id' => ['required', 'integer', 'exists:master_events,id'],
            'turnstile_token' => ['required', 'string'],
            'nama_tim' => ['required', 'string', 'max:150'],
            'nama_pendaftar' => ['nullable', 'string', 'max:150'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'nik_atlet_satu' => ['required', 'string', 'max:16'],
            'nama_atlet_satu' => ['required', 'string', 'max:150'],
            'tanggal_lahir_atlet_satu' => ['required', 'date'],
            'jenis_kelamin_atlet_satu' => ['required', 'in:Laki-laki,Perempuan'],
            'no_hp_atlet_satu' => ['required', 'string', 'max:20'],
            'nik_atlet_dua' => ['required', 'string', 'max:16'],
            'nama_atlet_dua' => ['required', 'string', 'max:150'],
            'tanggal_lahir_atlet_dua' => ['required', 'date'],
            'jenis_kelamin_atlet_dua' => ['required', 'in:Laki-laki,Perempuan'],
            'no_hp_atlet_dua' => ['required', 'string', 'max:20'],
        ];
    }

    /** @return array<string, string> */
    private function messages(): array
    {
        return ['required' => ':attribute wajib diisi.', 'email' => 'Email tidak valid.', 'exists' => 'Event yang dipilih tidak tersedia.', 'array' => 'Data peserta tidak valid.', 'min.array' => 'Minimal harus ada satu peserta.', 'date' => ':attribute harus berupa tanggal yang valid.', 'in' => 'Pilihan :attribute tidak valid.'];
    }

    /** @return array<string, string> */
    private function attributes(): array
    {
        return ['master_event_id' => 'event', 'nama_tim' => 'nama tim', 'nik_atlet_satu' => 'NIK atlet satu', 'nama_atlet_satu' => 'nama atlet satu', 'tanggal_lahir_atlet_satu' => 'tanggal lahir atlet satu', 'jenis_kelamin_atlet_satu' => 'jenis kelamin atlet satu', 'no_hp_atlet_satu' => 'nomor HP atlet satu', 'nik_atlet_dua' => 'NIK atlet dua', 'nama_atlet_dua' => 'nama atlet dua', 'tanggal_lahir_atlet_dua' => 'tanggal lahir atlet dua', 'jenis_kelamin_atlet_dua' => 'jenis kelamin atlet dua', 'no_hp_atlet_dua' => 'nomor HP atlet dua'];
    }

    private function pendaftaranAktif(MasterEvent $event): bool
    {
        $today = today()->toDateString();

        return $event->status === 'dibuka'
            && ($event->pendaftaran_mulai === null || $event->pendaftaran_mulai->toDateString() <= $today)
            && ($event->pendaftaran_selesai === null || $event->pendaftaran_selesai->toDateString() >= $today);
    }

    /** @param array<string, mixed> $validated */
    private function pastikanAtletBelumTerdaftar(MasterEvent $event, array $validated): void
    {
        $nikAtletSatu = trim((string) $validated['nik_atlet_satu']);
        $nikAtletDua = trim((string) $validated['nik_atlet_dua']);

        if ($nikAtletSatu === $nikAtletDua) {
            throw ValidationException::withMessages([
                'nik_atlet_dua' => 'NIK atlet dua harus berbeda dengan NIK atlet satu.',
            ]);
        }

        $rincis = PendaftaranEventRinci::query()
            ->whereHas('pendaftaran', fn ($query) => $query->where('master_event_id', $event->id))
            ->where(function ($query) use ($nikAtletSatu, $nikAtletDua): void {
                $query->whereIn('nik_atlet_satu', [$nikAtletSatu, $nikAtletDua])
                    ->orWhereIn('nik_atlet_dua', [$nikAtletSatu, $nikAtletDua]);
            })
            ->get(['nik_atlet_satu', 'nik_atlet_dua']);

        $nikTerdaftar = $rincis
            ->flatMap(fn (PendaftaranEventRinci $rinci) => [$rinci->nik_atlet_satu, $rinci->nik_atlet_dua])
            ->filter()
            ->map(fn (string $nik) => trim($nik));

        $errors = [];
        if ($nikTerdaftar->contains($nikAtletSatu)) {
            $errors['nik_atlet_satu'] = 'Atlet satu sudah terdaftar pada event ini.';
        }
        if ($nikTerdaftar->contains($nikAtletDua)) {
            $errors['nik_atlet_dua'] = 'Atlet dua sudah terdaftar pada event ini.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
