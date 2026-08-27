<?php

use App\Models\Club;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('akun club tidak dapat login ke aplikasi pengurus', function (): void {
    $user = User::factory()->create([
        'username' => 'club-login-test',
        'password' => 'password-club',
    ]);

    Club::create([
        'user_id' => $user->id,
        'nama_club' => 'Club Login Test',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'login' => 'club-login-test',
        'password' => 'password-club',
        'device_name' => 'orado-pengurus',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('login');

    expect($user->tokens()->count())->toBe(0);
});

test('akun non-club tetap dapat login ke aplikasi pengurus', function (): void {
    $user = User::factory()->create([
        'username' => 'pengurus-login-test',
        'password' => 'password-pengurus',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'login' => 'pengurus-login-test',
        'password' => 'password-pengurus',
        'device_name' => 'orado-pengurus',
    ])
        ->assertOk()
        ->assertJsonPath('data.token_type', 'Bearer');

    expect($user->tokens()->count())->toBe(1);
});

test('pengurus yang menjadi anggota club tetap dapat login ke aplikasi pengurus', function (): void {
    $clubOwner = User::factory()->create();
    $club = Club::create([
        'user_id' => $clubOwner->id,
        'nama_club' => 'Club Pengurus Test',
    ]);
    $pengurus = User::factory()->create([
        'username' => 'pengurus-anggota-club',
        'password' => 'password-pengurus',
    ]);

    Anggota::create([
        'user_id' => $pengurus->id,
        'club_id' => $club->id,
        'name' => 'Pengurus Anggota Club',
        'nik' => '3574010101010001',
        'flag' => 2,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'login' => 'pengurus-anggota-club',
        'password' => 'password-pengurus',
        'device_name' => 'orado-pengurus',
    ])
        ->assertOk()
        ->assertJsonPath('data.token_type', 'Bearer');

    expect($pengurus->tokens()->count())->toBe(1);
});
