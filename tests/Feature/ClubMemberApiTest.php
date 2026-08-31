<?php

use App\Models\Anggota;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $owner = User::factory()->create();
    $this->club = Club::create([
        'user_id' => $owner->id,
        'nama_club' => 'Club API Test',
    ]);
    Sanctum::actingAs($owner, ['club']);
});

test('nik yang sudah terhubung ke club tidak dapat ditambahkan kembali', function (): void {
    $pengurus = Anggota::create([
        'name' => 'Nama Pengurus Lama',
        'email' => 'pengurus@orado.local',
        'nik' => '3574010101010001',
        'no_hp' => '081111111111',
        'kelompok_jabatan' => '1',
        'flag' => 2,
    ]);

    $existingClub = Club::create([
        'nama_club' => 'Club Lain',
    ]);
    $pengurus->update(['club_id' => $existingClub->id]);

    $this->postJson('/api/v2/club/anggota', [
        'name' => 'Nama Tidak Boleh Mengubah',
        'email' => 'baru@example.com',
        'nik' => '3574010101010001',
        'jenis_kelamin' => 'L',
        'no_hp' => '082222222222',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('nik');

    $this->assertDatabaseHas('anggotas', [
        'id' => $pengurus->id,
        'club_id' => $existingClub->id,
        'name' => 'Nama Pengurus Lama',
        'email' => 'pengurus@orado.local',
        'no_hp' => '081111111111',
        'kelompok_jabatan' => '1',
    ]);
});

test('nik baru membuat anggota club dengan email otomatis dan kelompok jabatan dua', function (): void {
    $response = $this->postJson('/api/v2/club/anggota', [
        'name' => 'Anggota Club Baru',
        'email' => '',
        'nik' => '3574010101010002',
        'jenis_kelamin' => 'P',
        'no_hp' => '083333333333',
    ])->assertCreated();

    $memberId = $response->json('data.id');

    $this->assertDatabaseHas('anggotas', [
        'club_id' => $this->club->id,
        'name' => 'Anggota Club Baru',
        'nama' => 'Anggota Club Baru',
        'email' => "a-{$memberId}@o.id",
        'nik' => '3574010101010002',
        'no_hp' => '083333333333',
        'kelompok_jabatan' => '2',
        'flag' => 1,
    ]);
});

test('jenis kelamin anggota divalidasi oleh backend', function (): void {
    $this->postJson('/api/v2/club/anggota', [
        'name' => 'Anggota Tanpa Gender',
        'nik' => '3574010101010011',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('jenis_kelamin');
});

test('anggota baru dari club muncul dalam pengajuan pengurus', function (): void {
    $this->postJson('/api/v2/club/anggota', [
        'name' => 'Anggota Menunggu Verifikasi',
        'nik' => '3574010101010003',
        'jenis_kelamin' => 'L',
    ])->assertCreated();

    $pengurus = User::factory()->create();
    Sanctum::actingAs($pengurus, ['pengurus']);

    $this->getJson('/api/v1/master/anggota?status=menunggu')
        ->assertOk()
        ->assertJsonPath('data.data.0.nik', '3574010101010003')
        ->assertJsonPath('data.data.0.flag', 1);
});

test('anggota club dapat difilter berdasarkan kategori umur atlet', function (): void {
    $junior = Anggota::create([
        'club_id' => $this->club->id,
        'name' => 'Atlet Yunior',
        'nik' => '3574010101010006',
        'tanggal_lahir' => now()->subYears(16),
        'jenis_kelamin' => 'L',
        'kelompok_jabatan' => '2',
        'flag' => 2,
    ]);
    Anggota::create([
        'club_id' => $this->club->id,
        'name' => 'Atlet Senior',
        'nik' => '3574010101010007',
        'tanggal_lahir' => now()->subYears(20),
        'jenis_kelamin' => 'P',
        'kelompok_jabatan' => '2',
        'flag' => 2,
    ]);
    Anggota::create([
        'club_id' => $this->club->id,
        'name' => 'Atlet Profesional',
        'nik' => '3574010101010008',
        'tanggal_lahir' => now()->subYears(40),
        'jenis_kelamin' => 'L',
        'kelompok_jabatan' => '2',
        'flag' => 2,
    ]);

    $this->getJson('/api/v2/club/anggota?age_group=junior')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $junior->id);

    $this->getJson('/api/v2/club/anggota?gender=P')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Atlet Senior');
});

test('daftar anggota club hanya menampilkan anggota aktif', function (): void {
    Anggota::create([
        'club_id' => $this->club->id,
        'name' => 'Anggota Menunggu',
        'nik' => '3574010101010009',
        'kelompok_jabatan' => '2',
        'flag' => 1,
    ]);
    $activeMember = Anggota::create([
        'club_id' => $this->club->id,
        'name' => 'Anggota Aktif',
        'nik' => '3574010101010010',
        'kelompok_jabatan' => '2',
        'flag' => 2,
    ]);

    $this->getJson('/api/v2/club/anggota')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $activeMember->id);
});

test('club dapat melihat dan memperbarui biaya iuran', function (): void {
    $this->getJson('/api/v2/club/pengaturan/biaya-iuran')
        ->assertOk()
        ->assertJsonPath('data.biaya_iuran', 0);

    $this->putJson('/api/v2/club/pengaturan/biaya-iuran', ['biaya_iuran' => 25000])
        ->assertOk()
        ->assertJsonPath('data.biaya_iuran', 25000);

    $this->assertDatabaseHas('clubs', [
        'id' => $this->club->id,
        'biaya_iuran' => 25000,
    ]);
});

test('club dapat mencatat iuran satu kali untuk setiap anggota dan periode', function (): void {
    $this->club->update(['biaya_iuran' => 25000]);
    $member = Anggota::create([
        'club_id' => $this->club->id,
        'name' => 'Anggota Iuran',
        'nik' => '3574010101010012',
        'jenis_kelamin' => 'L',
        'kelompok_jabatan' => '2',
        'flag' => 2,
    ]);
    $payload = [
        'anggota_id' => $member->id,
        'periode' => '2026-08',
        'tanggal_bayar' => '2026-08-15',
    ];

    $this->postJson('/api/v2/club/iuran', $payload)
        ->assertCreated()
        ->assertJsonPath('data.anggota.id', $member->id)
        ->assertJsonPath('data.nominal', 25000);

    $this->getJson('/api/v2/club/iuran/anggota?search=Iuran')
        ->assertOk()
        ->assertJsonPath('data.0.id', $member->id);

    $this->postJson('/api/v2/club/iuran', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('periode');
});

test('anggota club bukan kehormatan dapat diverifikasi tanpa jabatan', function (): void {
    $member = Anggota::create([
        'name' => 'Anggota Club Biasa',
        'nik' => '3574010101010004',
        'club_id' => $this->club->id,
        'kelompok_jabatan' => '2',
        'flag' => 1,
    ]);

    $pengurus = User::factory()->create();
    Sanctum::actingAs($pengurus, ['pengurus']);

    $this->postJson("/api/v1/master/anggota/{$member->id}/verifikasi", [])
        ->assertOk()
        ->assertJsonPath('data.flag', 2)
        ->assertJsonPath('data.kelompok_jabatan', '2')
        ->assertJsonPath('data.jabatan', null);
});

test('anggota kehormatan tetap wajib mengisi jabatan saat verifikasi', function (): void {
    $member = Anggota::create([
        'name' => 'Anggota Kehormatan',
        'nik' => '3574010101010005',
        'kelompok_jabatan' => '1',
        'flag' => 1,
    ]);

    $pengurus = User::factory()->create();
    Sanctum::actingAs($pengurus, ['pengurus']);

    $this->postJson("/api/v1/master/anggota/{$member->id}/verifikasi", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('jabatan');
});
