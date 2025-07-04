<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produk>
 */
class ProdukFactory extends Factory
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
            'kategori_id' => Kategori::inRandomOrder()->first()->id_kategori ?? Kategori::factory(),
            'nama_produk' => $this->faker->words(2, true),
            'kode_produk' => strtoupper('PRD' . $this->faker->unique()->numberBetween(100, 999)),
            'harga_produk' => $this->faker->numberBetween(10000, 500000),
            'stok_produk' => 0, // default awal
            'deskripsi_produk' => $this->faker->sentence(),
            'gambar_produk' => null, // Kosongkan karena tidak diunggah
        ];
    }
}
