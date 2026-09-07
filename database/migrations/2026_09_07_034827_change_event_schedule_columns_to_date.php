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
        Schema::table('master_events', function (Blueprint $table): void {
            $table->date('tanggal_mulai')->change();
            $table->date('tanggal_selesai')->change();
            $table->date('pendaftaran_mulai')->nullable()->change();
            $table->date('pendaftaran_selesai')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_events', function (Blueprint $table): void {
            $table->dateTime('tanggal_mulai')->change();
            $table->dateTime('tanggal_selesai')->change();
            $table->dateTime('pendaftaran_mulai')->nullable()->change();
            $table->dateTime('pendaftaran_selesai')->nullable()->change();
        });
    }
};
