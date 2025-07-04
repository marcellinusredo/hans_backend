<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    protected $fillable = [
        'nama_pelanggan',
        'alamat_pelanggan',
        'nomor_telp_pelanggan'
    ];

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class,'pelanggan_id','id_pelanggan');
    }
}
