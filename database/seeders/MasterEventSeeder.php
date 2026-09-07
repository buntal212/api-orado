<?php

namespace Database\Seeders;

use App\Models\MasterEvent;
use Illuminate\Database\Seeder;

class MasterEventSeeder extends Seeder
{
    public function run(): void
    {
        MasterEvent::factory()->count(3)->create();
    }
}
