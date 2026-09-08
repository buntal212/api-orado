<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\User;
use App\Services\OradoNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberVerificationController extends Controller
{
    public function __construct(private readonly OradoNotificationService $notificationService) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nik' => ['required', 'digits:16', 'unique:anggotas,nik'],
            'tanggal_lahir' => ['nullable', 'date'],
            'no_hp' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        [$anggota, $user] = DB::transaction(function () use ($validated): array {
            $anggota = Anggota::create([
                'name' => $validated['name'],
                'nik' => $validated['nik'],
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'no_hp' => $validated['no_hp'],
                'kelompok_jabatan' => '1',
                'flag' => 1,
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'anggota_id' => $anggota->id,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                // 'pass' => $request->pass,
            ]);

            return [$anggota, $user];
        });

        $this->sendPengurusRegistrationNotification($anggota, $user);

        return response()->json([
            'message' => 'Pendaftaran anggota berhasil. Silakan hubungi admin untuk verifikasi.',
        ], 201);
    }

    public function find(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16'],
        ]);

        $anggota = Anggota::query()->with('user:id,anggota_id,username,email')
            ->where('nik', $validated['nik'])
            ->first();

        if (! $anggota) {
            throw ValidationException::withMessages([
                'nik' => ['Data anggota dengan NIK tersebut tidak ditemukan.'],
            ]);
        }

        if ($anggota->user) {
            throw ValidationException::withMessages([
                'nik' => ['Akun anggota ini sudah terdaftar.'],
            ]);
        }

        return response()->json([
            'message' => 'Data anggota ditemukan.',
            'data' => $anggota,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $anggota = Anggota::query()->where('nik', $validated['nik'])->first();

        if (! $anggota) {
            throw ValidationException::withMessages([
                'nik' => ['Data anggota dengan NIK tersebut tidak ditemukan.'],
            ]);
        }

        if ($anggota->user) {
            throw ValidationException::withMessages([
                'username' => ['Akun anggota ini sudah terdaftar.'],
            ]);
        }

        $user = User::create([
            'name' => $anggota->name,
            'anggota_id' => $anggota->id,
            'username' => $validated['username'],
            'email' => "a-{$anggota->id}@o.id",
            'password' => $validated['password'],
            'pass' => $validated['password'],
        ]);

        $this->sendPengurusRegistrationNotification($anggota, $user);

        return response()->json([
            'message' => 'Pendaftaran anggota berhasil. Silakan login menggunakan akun baru Anda.',
        ]);
    }

    private function sendPengurusRegistrationNotification(Anggota $anggota, User $user): void
    {
        $isAnggotaKehormatan = (string) $anggota->kelompok_jabatan === '1';

        $this->notificationService->sendToPengurus(
            $isAnggotaKehormatan ? 'Anggota Kehormatan Baru' : 'Pengurus Baru Mendaftar',
            $anggota->name.' telah melakukan pendaftaran.',
            [
                'type' => 'pengurus_registration',
                'user_id' => (string) $user->id,
                'url' => '/verifikasi-pengajuan',
                'menu_label' => 'Verifikasi & Pengajuan',
            ],
        );
    }
}
