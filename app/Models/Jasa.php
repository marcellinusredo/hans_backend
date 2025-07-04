<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jasa extends Model
{
    use HasFactory;

    protected $table = 'jasa';
    protected $primaryKey = 'id_jasa';
    protected $fillable = [
        'nama_jasa',
        'harga_jasa',
        'deskripsi_jasa'
    ];

    public function detail_transaksi_jasa(): HasMany
    {
        return $this->hasMany(DetailTransaksiJasa::class,'jasa_id','id_jasa');
    }
}
