<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            StaffSeeder::class,
        ]);
        /*
        \App\Models\Kategori::factory(5)->create();
        \App\Models\Jasa::factory(10)->create();
        \App\Models\Pelanggan::factory(10)->create();
        \App\Models\Supplier::factory(5)->create();
        \App\Models\Produk::factory(20)->create();
        \App\Models\PengadaanStok::factory(5)->create();
        \App\Models\DetailPengadaanStok::factory(5)->create();
        \App\Models\Transaksi::factory(5)->create();
        \App\Models\DetailTransaksi::factory(5)->create();
        \App\Models\DetailTransaksiJasa::factory(5)->create();
    */
        }
}
