<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail_transaksi';
    protected $fillable = [
        'produk_id',
        'transaksi_id',
        'jumlah_produk_detail_transaksi',
        'harga_produk_detail_transaksi',
        'subtotal_produk_detail_transaksi'
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class,'transaksi_id','id_transaksi');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class,'produk_id','id_produk');
    }
}
