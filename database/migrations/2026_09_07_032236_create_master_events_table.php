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
        Schema::create('master_events', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_event', 50)->unique();
            $table->string('nama_event', 150);
            $table->text('deskripsi')->nullable();
            $table->string('lokasi', 255)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('pendaftaran_mulai')->nullable();
            $table->date('pendaftaran_selesai')->nullable();
            $table->unsignedInteger('kuota_peserta')->nullable();
            $table->unsignedBigInteger('biaya_pendaftaran')->default(0);
            $table->string('poster')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_events');
    }
};
