<?php

namespace Database\Factories;

use App\Models\MasterJabatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterJabatan>
 */
class MasterJabatanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->jobTitle(),
        ];
    }
}
