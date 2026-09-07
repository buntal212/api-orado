<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_event_headers', function (Blueprint $table): void {
            $table->string('status_pendaftaran', 30)->default('terdaftar')->change();
        });

        Schema::table('pendaftaran_event_rincis', function (Blueprint $table): void {
            $table->string('status', 30)->default('terdaftar')->change();
        });

        DB::table('pendaftaran_event_headers')
            ->where('status_pendaftaran', 'menunggu')
            ->update(['status_pendaftaran' => 'terdaftar']);

        DB::table('pendaftaran_event_rincis')
            ->where('status', 'menunggu')
            ->update(['status' => 'terdaftar']);
    }

    public function down(): void
    {
        Schema::table('pendaftaran_event_headers', function (Blueprint $table): void {
            $table->string('status_pendaftaran', 30)->default('menunggu')->change();
        });

        Schema::table('pendaftaran_event_rincis', function (Blueprint $table): void {
            $table->string('status', 30)->default('menunggu')->change();
        });
    }
};
