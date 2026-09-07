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
        Schema::table('pendaftaran_event_rincis', function (Blueprint $table): void {
            $table->string('nik_atlet_dua', 16)->nullable()->after('no_hp_atlet_satu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_event_rincis', function (Blueprint $table): void {
            $table->dropColumn('nik_atlet_dua');
        });
    }
};
