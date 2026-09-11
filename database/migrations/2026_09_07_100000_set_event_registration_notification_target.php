<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $eventRegistrationType = DB::getDriverName() === 'sqlite'
            ? "json_extract(data, '$.type')"
            : "JSON_UNQUOTE(JSON_EXTRACT(data, '$.type'))";

        DB::statement("
            UPDATE pengurus_notifications
            SET data = JSON_SET(
                COALESCE(data, JSON_OBJECT()),
                '$.menu', 'event-peserta',
                '$.menu_label', 'Data Peserta Event',
                '$.url', '/event-peserta'
            )
            WHERE {$eventRegistrationType} = 'event_registration'
        ");
    }

    public function down(): void
    {
        $eventRegistrationType = DB::getDriverName() === 'sqlite'
            ? "json_extract(data, '$.type')"
            : "JSON_UNQUOTE(JSON_EXTRACT(data, '$.type'))";

        DB::statement("
            UPDATE pengurus_notifications
            SET data = JSON_REMOVE(data, '$.menu', '$.menu_label')
            WHERE {$eventRegistrationType} = 'event_registration'
        ");
    }
};
