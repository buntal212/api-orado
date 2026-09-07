<?php

namespace Database\Factories;

use App\Models\MasterEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterEvent>
 */
class MasterEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_event' => 'EVT-'.fake()->unique()->numerify('#####'),
            'nama_event' => fake()->sentence(3),
            'deskripsi' => fake()->paragraph(),
            'lokasi' => fake()->city(),
            'tanggal_mulai' => today()->addWeek()->toDateString(),
            'tanggal_selesai' => today()->addWeeks(2)->toDateString(),
            'pendaftaran_mulai' => today()->toDateString(),
            'pendaftaran_selesai' => today()->addDays(5)->toDateString(),
            'kuota_peserta' => fake()->numberBetween(10, 100),
            'biaya_pendaftaran' => fake()->numberBetween(0, 500000),
            'status' => 'draft',
        ];
    }
}
