<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PengadaanStok>
 */
class PengadaanStokFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
            // Ambil tanggal hari ini (bisa juga gunakan tanggal faker jika perlu)
    $tanggal = Carbon::now()->format('Ymd'); // 20250618

    // Hitung jumlah transaksi hari ini
    $count = DB::table('transaksi')
        ->whereDate('created_at', Carbon::today())
        ->count() + 1;

    // Format urutan jadi 4 digit
    $nomorUrut = str_pad($count, 4, '0', STR_PAD_LEFT); // 0001
        $faker = \Faker\Factory::create('id_ID');
        return [
            'nomor_invoice' => "INV-TRX-{$tanggal}-{$nomorUrut}",
            'supplier_id' => Supplier::inRandomOrder()->first()->id_supplier ?? Supplier::factory(),
            'staff_id' => Staff::inRandomOrder()->first()->id_staff ?? Staff::factory(),
            'waktu_pengadaan_stok' => $this->faker->date('y-m-d'),
            'total_harga_pengadaan_stok' => $this->faker->numberBetween(10000, 500000000),
        ];
    }
}
