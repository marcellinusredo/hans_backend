<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $table = 'role';
    protected $primaryKey = 'id_role';
    protected $fillable = [
        'nama_role'
    ];


    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class,'role_id','id_role');
    }
}
