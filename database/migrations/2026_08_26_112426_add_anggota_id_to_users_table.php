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
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('anggota_id')->nullable()->after('id')->constrained('anggotas')->nullOnDelete();
        });

        DB::table('users')->whereNotNull('nik')->orderBy('id')->each(function (object $user): void {
            $anggotaId = DB::table('anggotas')->insertGetId([
                'name' => $user->name,
                'nik' => $user->nik,
                'no_hp' => $user->no_hp,
                'kelompok_jabatan' => $user->kelompok_jabatan,
                'jabatan' => $user->jabatan,
                'flag' => $user->flag ?? 1,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

            DB::table('users')->where('id', $user->id)->update(['anggota_id' => $anggotaId]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('anggota_id');
        });
    }
};
