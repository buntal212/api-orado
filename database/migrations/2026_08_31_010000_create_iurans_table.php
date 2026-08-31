<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iurans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->foreignId('anggota_id')->constrained('anggotas')->cascadeOnDelete();
            $table->date('periode');
            $table->unsignedInteger('nominal');
            $table->date('tanggal_bayar');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['club_id', 'anggota_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iurans');
    }
};
