<?php

use App\Models\MasterEvent;
use App\Models\PendaftaranEventHeader;
use App\Services\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->mock(TurnstileService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('verify')->once()->andReturnTrue();
    });
});

test('pendaftaran event ditolak saat kuota tim telah penuh', function (): void {
    $event = MasterEvent::factory()->create([
        'status' => 'dibuka',
        'kuota_peserta' => 1,
    ]);

    PendaftaranEventHeader::create([
        'master_event_id' => $event->id,
        'kode_event' => $event->kode_event,
        'kode_pendaftaran' => 'REG-00001',
        'nama_tim' => 'Tim Yang Sudah Terdaftar',
        'nama_pendaftar' => 'Pendaftar Lama',
        'no_hp' => '081234567890',
        'jumlah_peserta' => 2,
        'total_biaya' => $event->biaya_pendaftaran,
        'status_pendaftaran' => 'terdaftar',
        'status_pembayaran' => 'belum_bayar',
    ]);

    $this->postJson('/api/v3/event/pendaftaran', [
        'master_event_id' => $event->id,
        'turnstile_token' => 'token-test',
        'nama_tim' => 'Tim Baru',
        'nik_atlet_satu' => '3574010101010001',
        'nama_atlet_satu' => 'Atlet Satu',
        'tanggal_lahir_atlet_satu' => '2000-01-01',
        'jenis_kelamin_atlet_satu' => 'Laki-laki',
        'no_hp_atlet_satu' => '081234567891',
        'nik_atlet_dua' => '3574010101010002',
        'nama_atlet_dua' => 'Atlet Dua',
        'tanggal_lahir_atlet_dua' => '2001-01-01',
        'jenis_kelamin_atlet_dua' => 'Perempuan',
        'no_hp_atlet_dua' => '081234567892',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('master_event_id');

    expect(PendaftaranEventHeader::where('master_event_id', $event->id)->count())->toBe(1);
});

test('kuota dua menerima dua tim meski setiap tim berisi dua atlet', function (): void {
    $event = MasterEvent::factory()->create([
        'status' => 'dibuka',
        'kuota_peserta' => 2,
    ]);

    PendaftaranEventHeader::create([
        'master_event_id' => $event->id,
        'kode_event' => $event->kode_event,
        'kode_pendaftaran' => 'REG-00001',
        'nama_tim' => 'Tim Pertama',
        'nama_pendaftar' => 'Pendaftar Pertama',
        'no_hp' => '081234567890',
        'jumlah_peserta' => 2,
        'total_biaya' => $event->biaya_pendaftaran,
        'status_pendaftaran' => 'terdaftar',
        'status_pembayaran' => 'belum_bayar',
    ]);

    $this->postJson('/api/v3/event/pendaftaran', [
        'master_event_id' => $event->id,
        'turnstile_token' => 'token-test',
        'nama_tim' => 'Tim Kedua',
        'nik_atlet_satu' => '3574010101010003',
        'nama_atlet_satu' => 'Atlet Tiga',
        'tanggal_lahir_atlet_satu' => '2000-01-01',
        'jenis_kelamin_atlet_satu' => 'Laki-laki',
        'no_hp_atlet_satu' => '081234567893',
        'nik_atlet_dua' => '3574010101010004',
        'nama_atlet_dua' => 'Atlet Empat',
        'tanggal_lahir_atlet_dua' => '2001-01-01',
        'jenis_kelamin_atlet_dua' => 'Perempuan',
        'no_hp_atlet_dua' => '081234567894',
    ])
        ->assertCreated();

    expect(PendaftaranEventHeader::where('master_event_id', $event->id)->count())->toBe(2);
});

test('nomor registrasi tetap unik saat ada data pendaftaran yang telah dihapus', function (): void {
    $event = MasterEvent::factory()->create(['status' => 'dibuka']);

    $pendaftaranPertama = null;
    foreach (range(1, 29) as $nomor) {
        $header = PendaftaranEventHeader::create([
            'master_event_id' => $event->id,
            'kode_event' => $event->kode_event,
            'kode_pendaftaran' => sprintf('REG-%05d', $nomor),
            'nama_tim' => "Tim {$nomor}",
            'nama_pendaftar' => "Pendaftar {$nomor}",
            'no_hp' => '081234567890',
            'jumlah_peserta' => 2,
            'total_biaya' => $event->biaya_pendaftaran,
            'status_pendaftaran' => 'terdaftar',
            'status_pembayaran' => 'belum_bayar',
        ]);

        if ($nomor === 1) {
            $pendaftaranPertama = $header;
        }
    }

    $pendaftaranPertama->delete();

    $this->postJson('/api/v3/event/pendaftaran', [
        'master_event_id' => $event->id,
        'turnstile_token' => 'token-test',
        'nama_tim' => 'Tim Baru',
        'nik_atlet_satu' => '3574010101010003',
        'nama_atlet_satu' => 'Atlet Tiga',
        'tanggal_lahir_atlet_satu' => '2000-01-01',
        'jenis_kelamin_atlet_satu' => 'Laki-laki',
        'no_hp_atlet_satu' => '081234567893',
        'nik_atlet_dua' => '3574010101010004',
        'nama_atlet_dua' => 'Atlet Empat',
        'tanggal_lahir_atlet_dua' => '2001-01-01',
        'jenis_kelamin_atlet_dua' => 'Perempuan',
        'no_hp_atlet_dua' => '081234567894',
    ])
        ->assertCreated()
        ->assertJsonPath('data.kode_pendaftaran', 'REG-00030');
});
