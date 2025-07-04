<?php

use Illuminate\Support\Facades\DB;

function generateNomorInvoiceSafe($tipe = 'TRX')
{
    return DB::transaction(function () use ($tipe) {
    // Buat prefix invoice berdasarkan tipe dan tanggal hari ini
    $prefix = 'INV-' . $tipe . '-' . now()->format('Ymd') . '-';

    // Tentukan nama tabel dan kolom invoice sesuai tipe
    $table = $tipe === 'TRX' ? 'transaksi' : 'pengadaan_stok';
    $field = $tipe === 'TRX' ? 'nomor_invoice_transaksi' : 'nomor_invoice_pengadaan_stok';

    // Hitung jumlah data hari ini untuk membuat nomor urut
    $count = DB::table($table)
        ->whereDate('created_at', now())
        ->sharedLock() // cegah race condition saat membaca
        ->count() + 1;

    // Buat nomor invoice akhir
    $no = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

    // Cek apakah nomor invoice sudah ada, jika ya, batalkan proses
    if (DB::table($table)->where($field, $no)->exists()) {
        throw new \Exception("Nomor invoice sudah ada, coba lagi.");
    }

        return $no;
    });
}
