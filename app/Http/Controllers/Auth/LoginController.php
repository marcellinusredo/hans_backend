<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        //validasi input
        $request->validate([
            'username_staff' => 'required',
            'password_staff' => 'required',
        ]);

        //validasi username
        $staff = Staff::where('username_staff', $request->username_staff)->first();
        if (!$staff) {
            return response()->json([
                'message' => 'Username tidak ditemukan.'
            ], 401);
        }

        //validasi password
        if (!Hash::check($request->password_staff, $staff->password_staff)) {
            return response()->json([
                'message' => 'Password salah.'
            ], 401);
        }

        // Hapus token lama dan buat baru
        $staff->tokens()->delete();
        $token = $staff->createToken('token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $staff->load('role')
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
