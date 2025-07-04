<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengadaanStok extends Model
{
    use HasFactory;

    protected $table = 'detail_pengadaan_stok';
    protected $primaryKey = 'id_detail_pengadaan_stok';
    protected $fillable = [
        'produk_id',
        'pengadaan_stok_id',
        'harga_produk_detail_pengadaan_stok',
        'jumlah_produk_detail_pengadaan_stok',
        'subtotal_produk_detail_pengadaan_stok'
    ];

    public function pengadaan_stok(): BelongsTo
    {
        return $this->belongsTo(PengadaanStok::class,'pengadaan_stok_id','id_pengadaan_stok');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class,'produk_id','id_produk');
    }
}
