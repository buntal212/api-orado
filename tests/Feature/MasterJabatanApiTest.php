<?php

use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Sanctum::actingAs(User::factory()->create());
});

test('master jabatan can be listed and managed with get and post methods', function (): void {
    MasterJabatan::factory()->create(['nama' => 'Bendahara']);

    $this->getJson('/api/v1/master/jabatan')
        ->assertOk()
        ->assertJsonPath('data.data.0.nama', 'Bendahara');

    $jabatan = $this->postJson('/api/v1/master/jabatan/simpan', ['nama' => 'Ketua Umum'])
        ->assertCreated()
        ->assertJsonPath('data.nama', 'Ketua Umum');

    $this->postJson("/api/v1/master/jabatan/{$jabatan->json('data.id')}/edit", ['nama' => 'Ketua Harian'])
        ->assertOk()
        ->assertJsonPath('data.nama', 'Ketua Harian');

    $this->postJson("/api/v1/master/jabatan/{$jabatan->json('data.id')}/hapus")
        ->assertOk();

    $this->assertDatabaseMissing('master_jabatans', ['nama' => 'Ketua Harian']);
});
