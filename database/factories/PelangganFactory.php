<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('id_ID');
        return [
            'nama_pelanggan' => $this->faker->name(),
            'alamat_pelanggan' => $this->faker->address(),
            'nomor_telp_pelanggan' => $this->faker->phoneNumber(),
        ];
    }
}
