<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengadaanStok extends Model
{
    use HasFactory;

    protected $table = 'pengadaan_stok';
    protected $primaryKey = 'id_pengadaan_stok';
    protected $fillable = [
        'supplier_id',
        'staff_id',
        'nomor_invoice_pengadaan_stok',
        'waktu_pengadaan_stok',
        'total_harga_pengadaan_stok'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class,'supplier_id','id_supplier');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class,'staff_id','id_staff');
    }

    public function detail_pengadaan_stok(): HasMany
    {
        return $this->hasMany(DetailPengadaanStok::class,'pengadaan_stok_id','id_pengadaan_stok');
    }

    // Perhitungan Total Harga
     public static function updateTotalHarga($pengadaan_stok_id)
    {
        $total = DetailPengadaanStok::where('pengadaan_stok_id', $pengadaan_stok_id)->sum('subtotal_produk_detail_pengadaan_stok');

        PengadaanStok::where('id_pengadaan_stok', $pengadaan_stok_id)->update(['total_harga_pengadaan_stok' => $total]);
    }
}
