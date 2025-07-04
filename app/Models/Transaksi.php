<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    protected $fillable = [
        'pelanggan_id',
        'staff_id',
        'nomor_invoice_transaksi',
        'waktu_transaksi',
        'total_harga_transaksi',
        'pembayaran_transaksi',
        'kembalian_transaksi'
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class,'pelanggan_id','id_pelanggan');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class,'staff_id','id_staff');
    }

    public function detail_transaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class,'transaksi_id','id_transaksi');
    }

    public function detail_transaksi_jasa(): HasMany
    {
        return $this->hasMany(DetailTransaksiJasa::class,'transaksi_id','id_transaksi');
    }

    // Perhitungan Total Harga
     public static function updateTotalHarga($transaksi_id)
    {
        $totalProduk = DetailTransaksi::where('transaksi_id', $transaksi_id)->sum('subtotal_produk_detail_transaksi');
        $totalJasa = DetailTransaksiJasa::where('transaksi_id', $transaksi_id)->sum('harga_jasa_detail_transaksi_jasa');

        $total = $totalProduk + $totalJasa;

        Transaksi::where('id_transaksi', $transaksi_id)->update(['total_harga_transaksi' => $total]);
    }
}
