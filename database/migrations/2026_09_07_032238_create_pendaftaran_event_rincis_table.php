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
        Schema::create('pendaftaran_event_rincis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pendaftaran_event_header_id')
                ->constrained('pendaftaran_event_headers')
                ->cascadeOnDelete();
            $table->foreignId('anggota_id')->nullable()->constrained('anggotas')->nullOnDelete();
            $table->string('nama_peserta', 150);
            $table->string('nik', 16)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('kategori', 100)->nullable();
            $table->string('kelas_lomba', 100)->nullable();
            $table->string('nomor_peserta', 50)->nullable();
            $table->unsignedBigInteger('biaya_pendaftaran')->default(0);
            $table->string('status', 30)->default('terdaftar');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(
                ['pendaftaran_event_header_id', 'anggota_id'],
                'pendaftaran_event_rinci_header_anggota_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_event_rincis');
    }
};
