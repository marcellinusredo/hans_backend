<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';
    protected $primaryKey = 'id_supplier';
    protected $fillable = [
        'nama_supplier',
        'nomor_telp_supplier',
        'alamat_supplier'
    ];

    public function pengadaanStok(): HasMany
    {
        return $this->hasMany(PengadaanStok::class,'supplier_id','id_supplier');
    }
}
