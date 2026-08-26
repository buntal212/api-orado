<?php

namespace Database\Seeders;

use App\Models\MasterJabatan;
use Illuminate\Database\Seeder;

class MasterJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MasterJabatan::factory()->count(5)->create();
    }
}
