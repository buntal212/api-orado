<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clubs')
            ->where(function ($query): void {
                $query->whereNull('kode_club')
                    ->orWhere('kode_club', '');
            })
            ->orderBy('id')
            ->each(function (object $club): void {
                DB::table('clubs')
                    ->where('id', $club->id)
                    ->update(['kode_club' => $club->id.'-OR-PRB']);
            });
    }

    public function down(): void
    {
        DB::table('clubs')
            ->where('kode_club', 'like', '%-OR-PRB')
            ->update(['kode_club' => null]);
    }
};
