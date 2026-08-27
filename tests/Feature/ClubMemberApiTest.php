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

test('nik pengurus yang sudah ada hanya diperbarui club id', function (): void {
    $pengurus = Anggota::create([
        'name' => 'Nama Pengurus Lama',
        'email' => 'pengurus@orado.local',
        'nik' => '3574010101010001',
        'no_hp' => '081111111111',
        'kelompok_jabatan' => '1',
        'flag' => 2,
    ]);

    $this->postJson('/api/v2/club/anggota', [
        'name' => 'Nama Tidak Boleh Mengubah',
        'email' => 'baru@example.com',
        'nik' => '3574010101010001',
        'no_hp' => '082222222222',
    ])
        ->assertOk()
        ->assertJsonPath('data.id', $pengurus->id);

    $this->assertDatabaseHas('anggotas', [
        'id' => $pengurus->id,
        'club_id' => $this->club->id,
        'name' => 'Nama Pengurus Lama',
        'email' => 'pengurus@orado.local',
        'no_hp' => '081111111111',
        'kelompok_jabatan' => '1',
    ]);
});

test('nik baru membuat anggota club dengan email otomatis dan kelompok jabatan dua', function (): void {
    $this->postJson('/api/v2/club/anggota', [
        'name' => 'Anggota Club Baru',
        'email' => '',
        'nik' => '3574010101010002',
        'no_hp' => '083333333333',
    ])->assertCreated();

    $this->assertDatabaseHas('anggotas', [
        'club_id' => $this->club->id,
        'name' => 'Anggota Club Baru',
        'nama' => 'Anggota Club Baru',
        'email' => 'anggota-3574010101010002@orado.local',
        'nik' => '3574010101010002',
        'no_hp' => '083333333333',
        'kelompok_jabatan' => '2',
        'flag' => 1,
    ]);
});

test('anggota baru dari club muncul dalam pengajuan pengurus', function (): void {
    $this->postJson('/api/v2/club/anggota', [
        'name' => 'Anggota Menunggu Verifikasi',
        'nik' => '3574010101010003',
    ])->assertCreated();

    $pengurus = User::factory()->create();
    Sanctum::actingAs($pengurus, ['pengurus']);

    $this->getJson('/api/v1/master/anggota?status=menunggu')
        ->assertOk()
        ->assertJsonPath('data.data.0.nik', '3574010101010003')
        ->assertJsonPath('data.data.0.flag', 1);
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
