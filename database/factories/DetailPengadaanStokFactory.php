<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DetailPengadaanStok;
use App\Models\PengadaanStok;
use App\Models\Produk;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailPengadaanStok>
 */
class DetailPengadaanStokFactory extends Factory
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
            'pengadaan_stok_id' => PengadaanStok::inRandomOrder()->first()->id_pengadaan_stok ?? PengadaanStok::factory(),
            'produk_id' => Produk::inRandomOrder()->first()->id_produk ?? Produk::factory(),
            'harga_produk_detail_pengadaan_stok' => $this->faker->numberBetween(10000, 500000),
            'jumlah_produk_detail_pengadaan_stok' => $this->faker->numberBetween(1, 10),
            'subtotal_produk_detail_pengadaan_stok' => $this->faker->numberBetween(10000, 500000000),
        ];
    }
}
