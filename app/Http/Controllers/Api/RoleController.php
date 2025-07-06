<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    //GET /Menampilkan semua data
    public function index()
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Pemilik']);

            // Ambil role user yang login
            $loggedInRole = Auth::user()->role->nama_role ?? '';

            // Ambil semua role atau hanya yang bukan Super Admin
            $roles = Role::when($loggedInRole !== 'Super Admin', function ($query) {
                $query->where('nama_role', '!=', 'Super Admin');
            })->get();

            return response()->json([
                'status' => true,
                'data' => $roles
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Role',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //POST /Menambah Data
    public function store(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Pemilik']);

            //validasi input
            $validator = Validator::make($request->all(), [
                'nama_role' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //buat data
            $role = Role::create([
                'nama_role' => $request->nama_role
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Role berhasil ditambahkan',
                'data' => $role
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal buat data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //GET /Menampilkan data tertentu
    public function show(string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Pemilik']);

            //cari data
            $role = Role::find($id);
            if (!$role) {
                return response()->json([
                    'status' => false,
                    'message' => 'Role tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $role
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //PUT / Update data tertentu
    public function update(Request $request, string $id)
    {
        try {
            //validasi input
            RoleHelper::allowOnly(['Super Admin', 'Pemilik']);

            //cari data
            $role = Role::find($id);
            if (!$role) {
                return response()->json([
                    'status' => false,
                    'message' => 'Role tidak ditemukan'
                ], 404);
            }

            //validasi input
            $validator = Validator::make($request->all(), [
                'nama_role' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //update data
            $role->update([
                'nama_role' => $request->nama_role
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Role berhasil diupdate',
                'data' => $role
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //DELETE / Hapus data tertentu
    public function destroy(string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Pemilik']);

            //cari data
            $role = Role::find($id);
            if (!$role) {
                return response()->json([
                    'status' => false,
                    'message' => 'Role tidak ditemukan'
                ], 404);
            }

            //hapus data
            $role->delete();

            return response()->json([
                'status' => true,
                'message' => 'Role berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
