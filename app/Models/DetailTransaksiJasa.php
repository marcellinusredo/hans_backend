<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksiJasa extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi_jasa';
    protected $primaryKey = 'id_detail_transaksi_jasa';
    protected $fillable = [
        'transaksi_id',
        'jasa_id',
        'harga_jasa_detail_transaksi_jasa',
    ];

    public function jasa(): BelongsTo
    {
        return $this->belongsTo(Jasa::class,'jasa_id','id_jasa');
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class,'transaksi_id','id_transaksi');
    }
}
