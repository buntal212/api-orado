<?php

use App\Models\MasterEvent;
use App\Models\PendaftaranEventHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('data peserta event selalu dibatasi lima belas tim per halaman', function (): void {
    Sanctum::actingAs(User::factory()->create());
    $event = MasterEvent::factory()->create();

    foreach (range(1, 16) as $nomor) {
        PendaftaranEventHeader::create([
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
    }

    $this->getJson('/api/v3/event/peserta?per_page=100')
        ->assertOk()
        ->assertJsonCount(15, 'data.data')
        ->assertJsonPath('data.per_page', 15)
        ->assertJsonPath('data.current_page', 1)
        ->assertJsonPath('data.next_page_url', fn ($url) => $url !== null);
});

test('data peserta event dapat difilter berdasarkan event yang dipilih', function (): void {
    Sanctum::actingAs(User::factory()->create());
    $eventDipilih = MasterEvent::factory()->create();
    $eventLain = MasterEvent::factory()->create();

    foreach ([$eventDipilih, $eventLain] as $nomor => $event) {
        PendaftaranEventHeader::create([
            'master_event_id' => $event->id,
            'kode_event' => $event->kode_event,
            'kode_pendaftaran' => sprintf('REG-%05d', $nomor + 1),
            'nama_tim' => "Tim {$nomor}",
            'nama_pendaftar' => "Pendaftar {$nomor}",
            'no_hp' => '081234567890',
            'jumlah_peserta' => 2,
            'total_biaya' => $event->biaya_pendaftaran,
            'status_pendaftaran' => 'terdaftar',
            'status_pembayaran' => 'belum_bayar',
        ]);
    }

    $this->getJson("/api/v3/event/peserta?master_event_id={$eventDipilih->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.master_event_id', $eventDipilih->id);

    $this->getJson("/api/v3/event/peserta/cetak?master_event_id={$eventDipilih->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.master_event_id', $eventDipilih->id)
        ->assertJsonPath('meta.event.id', $eventDipilih->id);
});
