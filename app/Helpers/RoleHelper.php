<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RoleHelper
{
    /**
     * Mengecek apakah user yang sedang login memiliki salah satu dari role yang diizinkan.
     * Jika tidak, return response error 403.
     *
     * @param array $allowedRoles
     * @return void
     */
    public static function allowOnly(array $allowedRoles)
    {
        //Ambil data login
        $user = Auth::user();

        //Cek user login atau nama role tidak diberi akses
        if (!$user || !in_array($user->role->nama_role, $allowedRoles)) {
            abort(response()->json([
                'message' => 'Akses ditolak: hanya untuk role ' . implode(', ', $allowedRoles)
            ], 403));
        }
    }
}
