<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    protected $fillable = [
        'kategori_id',
        'nama_produk',
        'kode_produk',
        'harga_produk',
        'stok_produk',
        'deskripsi_produk',
        'gambar_produk'
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id_kategori');
    }

    public function detail_pengadaan_stok(): HasMany
    {
        return $this->hasMany(DetailPengadaanStok::class, 'produk_id', 'id_produk');
    }

    public function detail_transaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'produk_id', 'id_produk');
    }

    public static function tambahStok($produk_id, $jumlah)
    {
        $produk = self::find($produk_id);
        if ($produk) {
            $produk->stok_produk += max(0, $jumlah); // pastikan jumlah tidak negatif
            $produk->save();
        }
    }

    public static function kurangiStok($produk_id, $jumlah)
    {
        $produk = self::find($produk_id);
        if ($produk) {
            $produk->stok_produk = max(0, $produk->stok_produk - max(0, $jumlah)); // tidak bisa di bawah 0
            $produk->save();
        }
    }
}
