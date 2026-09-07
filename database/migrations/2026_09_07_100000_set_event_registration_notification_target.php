<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE pengurus_notifications
            SET data = JSON_SET(
                COALESCE(data, JSON_OBJECT()),
                '$.menu', 'event-peserta',
                '$.menu_label', 'Data Peserta Event',
                '$.url', '/event-peserta'
            )
            WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'event_registration'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE pengurus_notifications
            SET data = JSON_REMOVE(data, '$.menu', '$.menu_label')
            WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'event_registration'
        ");
    }
};
