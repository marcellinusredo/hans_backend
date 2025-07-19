<?php

namespace App\Http\Controllers\Api;

use App\Models\Kategori;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{
    //GET /Menampilkan semua data
    public function index(Request $request)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            // Ambil parameter dari request dengan default jika tidak ada
            $perPage = $request->input('per_page', 5);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'nama_kategori');
            $sortDir = $request->input('sort_dir', 'asc');

            // Validasi kolom yang boleh untuk sorting agar aman dari injection
            $allowedSort = ['id_kategori', 'nama_kategori'];
            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'nama_kategori';
            }
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

            // Bangun query
            $query = Kategori::select('id_kategori', 'nama_kategori', 'deskripsi_kategori');

            // Tambahkan filter pencarian jika ada
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_kategori', 'like', '%' . $search . '%');
                });
            }

            // Tambahkan sorting
            $query->orderBy($sortBy, $sortDir);

            // Ambil data dengan pagination
            $kategoris = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data hasil pagination
            $data = $kategoris->getCollection()->map(function ($kategori) {
                return [
                    'id_kategori' => $kategori->id_kategori,
                    'nama_kategori' => $kategori->nama_kategori,
                    'deskripsi_kategori' => $kategori->deskripsi_kategori,

                ];
            });

            // Kembalikan response JSON lengkap dengan info pagination
            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $kategoris->currentPage(),
                    'last_page' => $kategoris->lastPage(),
                    'per_page' => $kategoris->perPage(),
                    'total' => $kategoris->total(),
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Kategori',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //POST /Menambah Data
    public function store(Request $request)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //Validasi input
            $validator = Validator::make($request->all(), [
                'nama_kategori' => 'required|string|min:1|max:25',
                'deskripsi_kategori' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //Membuat data
            $kategori = Kategori::create([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi_kategori' => $request->deskripsi_kategori,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil ditambahkan',
                'data' => $kategori
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal tambah data Kategori',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //GET /Menampilkan data tertentu
    public function show(string $id)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //Cari data
            $kategori = Kategori::select('nama_kategori', 'deskripsi_kategori')
                ->where('id_kategori', $id)
                ->first();

            if (!$kategori) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data kategori tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $kategori
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Kategori',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //PUT / Update data tertentu
    public function update(Request $request, string $id)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //Cari data
            $kategori = Kategori::find($id);

            if (!$kategori) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            //Validasi input
            $validator = Validator::make($request->all(), [
                'nama_kategori' => 'required|string|min:1|max:25',
                'deskripsi_kategori' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //Update data
            $kategori->update([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi_kategori' => $request->deskripsi_kategori,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil diupdate',
                'data' => $kategori
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update data Kategori',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //DELETE / Hapus data tertentu
    public function destroy(string $id)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //Mencari data
            $kategori = Kategori::find($id);
            if (!$kategori) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            //Hapus data
            $kategori->delete();

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus data Kategori',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
