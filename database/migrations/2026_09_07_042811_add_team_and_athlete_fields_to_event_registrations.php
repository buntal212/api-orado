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
        Schema::table('pendaftaran_event_headers', function (Blueprint $table): void {
            $table->string('nama_tim', 150)->nullable()->after('kode_pendaftaran');
        });

        Schema::table('pendaftaran_event_rincis', function (Blueprint $table): void {
            $table->string('nik_atlet_satu', 16)->nullable()->after('anggota_id');
            $table->string('nama_atlet_satu', 150)->nullable()->after('nik_atlet_satu');
            $table->date('tanggal_lahir_atlet_satu')->nullable()->after('nama_atlet_satu');
            $table->string('jenis_kelamin_atlet_satu', 20)->nullable()->after('tanggal_lahir_atlet_satu');
            $table->string('no_hp_atlet_satu', 20)->nullable()->after('jenis_kelamin_atlet_satu');
            $table->string('nama_atlet_dua', 150)->nullable()->after('no_hp_atlet_satu');
            $table->date('tanggal_lahir_atlet_dua')->nullable()->after('nama_atlet_dua');
            $table->string('jenis_kelamin_atlet_dua', 20)->nullable()->after('tanggal_lahir_atlet_dua');
            $table->string('no_hp_atlet_dua', 20)->nullable()->after('jenis_kelamin_atlet_dua');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_event_rincis', function (Blueprint $table): void {
            $table->dropColumn(['nik_atlet_satu', 'nama_atlet_satu', 'tanggal_lahir_atlet_satu', 'jenis_kelamin_atlet_satu', 'no_hp_atlet_satu', 'nama_atlet_dua', 'tanggal_lahir_atlet_dua', 'jenis_kelamin_atlet_dua', 'no_hp_atlet_dua']);
        });

        Schema::table('pendaftaran_event_headers', function (Blueprint $table): void {
            $table->dropColumn('nama_tim');
        });
    }
};
