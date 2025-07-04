<?php

namespace Database\Factories;

use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailTransaksi>
 */
class DetailTransaksiFactory extends Factory
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
            'produk_id' => Produk::inRandomOrder()->first()->id_produk ?? Produk::factory(),
            'transaksi_id' => Transaksi::inRandomOrder()->first()->id_transaksi ?? Transaksi::factory(),
            'jumlah_produk_detail_transaksi' => $this->faker->numberBetween(1, 10),
            'harga_produk_detail_transaksi' => $this->faker->numberBetween(10000, 500000),
            'subtotal_produk_detail_transaksi' => $this->faker->numberBetween(10000, 500000000),
        ];
    }
}
