<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pendaftaran_event_headers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('master_event_id')->constrained('master_events')->restrictOnDelete();
            $table->string('kode_pendaftaran', 50)->unique();
            $table->foreignId('anggota_id')->nullable()->constrained('anggotas')->nullOnDelete();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->string('nama_pendaftar', 150);
            $table->string('no_hp', 20);
            $table->string('email')->nullable();
            $table->unsignedInteger('jumlah_peserta')->default(0);
            $table->unsignedBigInteger('total_biaya')->default(0);
            $table->string('status_pendaftaran', 30)->default('menunggu');
            $table->string('status_pembayaran', 30)->default('belum_bayar');
            $table->string('bukti_pembayaran')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_event_headers');
    }
};
