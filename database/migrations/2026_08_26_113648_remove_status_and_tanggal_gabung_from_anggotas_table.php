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
        Schema::table('anggotas', function (Blueprint $table): void {
            $table->dropColumn(['tanggal_gabung', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggotas', function (Blueprint $table): void {
            $table->date('tanggal_gabung')->nullable()->after('foto');
            $table->string('status')->nullable()->after('tanggal_gabung');
        });
    }
};
