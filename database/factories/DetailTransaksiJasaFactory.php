<?php

namespace Database\Factories;

use App\Models\Jasa;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailTransaksiJasa>
 */
class DetailTransaksiJasaFactory extends Factory
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
            'jasa_id' => Jasa::inRandomOrder()->first()->id_jasa ?? Jasa::factory(),
            'transaksi_id' => Transaksi::inRandomOrder()->first()->id_transaksi ?? Transaksi::factory(),
            'harga_jasa_detail_transaksi_jasa' => $this->faker->numberBetween(10000, 500000),
        ];
    }
}
