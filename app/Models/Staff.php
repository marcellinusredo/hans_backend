<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use HasFactory,HasApiTokens,Notifiable;

    protected $table = 'staff';
    protected $primaryKey = 'id_staff';
    protected $fillable = [
        'role_id',
        'nama_staff',
        'nomor_telp_staff',
        'alamat_staff',
        'username_staff',
        'password_staff'
    ];
     protected $hidden = [
        'password_staff'
    ];

    protected $casts = [
        'password_staff' => 'hashed', // Laravel 11 otomatis melakukan hashing pada saat assign password
    ];

    public function getAuthPassword()
    {
        return $this->password_staff;
    }

    public function getAuthIdentifierName()
    {
        return 'username_staff';
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class,'role_id','id_role');
    }
    
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class,'staff_id','id_staff');
    }

    public function pengadaanStok(): HasMany
    {
        return $this->hasMany(PengadaanStok::class,'staff_id','id_staff');
    }

}
