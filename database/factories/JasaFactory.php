<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jasa>
 */
class JasaFactory extends Factory
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
            'nama_jasa' => $this->faker->word(),
            'harga_jasa' => $this->faker->numberBetween(10000, 100000),
            'deskripsi_jasa' => $this->faker->sentence(),
        ];
    }
}
