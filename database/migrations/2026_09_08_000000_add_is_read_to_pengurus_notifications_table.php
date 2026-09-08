<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengurus_notifications', function (Blueprint $table): void {
            $table->unsignedTinyInteger('is_read')->default(0)->index()->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('pengurus_notifications', function (Blueprint $table): void {
            $table->dropIndex(['is_read']);
            $table->dropColumn('is_read');
        });
    }
};
