<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('anggotas', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('club_id')->nullable()->after('user_id');
            $table->string('no_anggota')->nullable()->unique()->after('club_id');
            $table->string('nama')->nullable()->after('name');
            $table->string('email')->nullable()->after('nama');
            $table->text('alamat')->nullable()->after('no_hp');
            $table->string('foto')->nullable()->after('alamat');
            $table->date('tanggal_gabung')->nullable()->after('foto');
            $table->string('status')->nullable()->after('tanggal_gabung');
        });

        DB::table('users')->whereNotNull('anggota_id')->orderBy('id')->each(function (object $user): void {
            DB::table('anggotas')->where('id', $user->anggota_id)->update([
                'user_id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggotas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropUnique(['no_anggota']);
            $table->dropColumn(['club_id', 'no_anggota', 'nama', 'email', 'alamat', 'foto', 'tanggal_gabung', 'status']);
        });
    }
};
