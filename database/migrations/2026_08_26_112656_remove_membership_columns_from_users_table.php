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
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['nik']);
            $table->dropColumn(['nik', 'no_hp', 'kelompok_jabatan', 'jabatan', 'flag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('nik', 16)->nullable()->unique()->after('username');
            $table->string('no_hp', 20)->nullable()->after('nik');
            $table->string('kelompok_jabatan')->nullable()->after('email');
            $table->string('jabatan')->nullable()->after('kelompok_jabatan');
            $table->unsignedTinyInteger('flag')->default(1)->after('no_hp');
        });
    }
};
