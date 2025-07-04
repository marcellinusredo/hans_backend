<?php

namespace App\Http\Controllers\Api;

use App\Models\Jasa;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class JasaController extends Controller
{
    //GET /Menampilkan semua data
    public function index(Request $request)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin', 'Kasir']);

            // Ambil parameter dari request dengan default jika tidak ada
            $perPage = $request->input('per_page', 5);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'nama_jasa');
            $sortDir = $request->input('sort_dir', 'asc');

            // Validasi kolom yang boleh untuk sorting agar aman dari injection
            $allowedSort = ['id_jasa', 'nama_jasa'];
            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'nama_jasa';
            }
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

            // Bangun query
            $query = Jasa::select('id_jasa', 'nama_jasa', 'harga_jasa', 'deskripsi_jasa');

            // Tambahkan filter pencarian jika ada
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_jasa', 'like', '%' . $search . '%');
                });
            }

            // Tambahkan sorting
            $query->orderBy($sortBy, $sortDir);

            // Ambil data dengan pagination
            $jasas = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data hasil pagination
            $data = $jasas->getCollection()->map(function ($jasa) {
                return [
                    'id_jasa' => $jasa->id_jasa,
                    'nama_jasa' => $jasa->nama_jasa,
                    'harga_jasa' => $jasa->harga_jasa,
                    'deskripsi_jasa' => $jasa->deskripsi_jasa,
                ];
            });

            // Kembalikan response JSON lengkap dengan info pagination
            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $jasas->currentPage(),
                    'last_page' => $jasas->lastPage(),
                    'per_page' => $jasas->perPage(),
                    'total' => $jasas->total(),
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Jasa',
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
                'nama_jasa' => 'required|string|max:255',
                'harga_jasa' => 'required|numeric|min:0',
                'deskripsi_jasa' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //Membuat data jasa
            $jasa = Jasa::create([
                'nama_jasa' => $request->nama_jasa,
                'harga_jasa' => $request->harga_jasa,
                'deskripsi_jasa' => $request->deskripsi_jasa,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Jasa berhasil ditambahkan',
                'data' => $jasa
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal tambah data Jasa',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //GET /Menampilkan data tertentu
    public function show(string $id)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin', 'Kasir']);

            //Cari dan filter data yang ditampilkan
            $jasa = Jasa::select('id_jasa', 'nama_jasa', 'harga_jasa', 'deskripsi_jasa')
                ->where('id_jasa', $id)
                ->first();

            if (!$jasa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data jasa tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $jasa
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Jasa',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //PUT / Update data tertentu
    public function update(Request $request, string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //Cari data yang akan diupdate
            $jasa = Jasa::find($id);
            if (!$jasa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jasa tidak ditemukan'
                ], 404);
            }

            //validasi input
            $validator = Validator::make($request->all(), [
                'nama_jasa' => 'required|string|max:255',
                'harga_jasa' => 'required|numeric|min:0',
                'deskripsi_jasa' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //Update data
            $jasa->update([
                'id_jasa' => $request->id_jasa,
                'nama_jasa' => $request->nama_jasa,
                'harga_jasa' => $request->harga_jasa,
                'deskripsi_jasa' => $request->deskripsi_jasa,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Jasa berhasil diupdate',
                'data' => $jasa
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update data Jasa',
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

            //Cari data
            $jasa = Jasa::find($id);
            if (!$jasa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jasa tidak ditemukan'
                ], 404);
            }

            //Hapus data
            $jasa->delete();

            return response()->json([
                'status' => true,
                'message' => 'Jasa berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus data Jasa',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function getDropdown()
    {
        try {
            //Validasi ROle
            RoleHelper::allowOnly(['Super Admin', 'Admin', 'Kasir']);

            // Filter data
            $jasa = Jasa::select('id_jasa', 'nama_jasa','harga_jasa')->get();

            return response()->json([
                'status' => true,
                'data' => $jasa
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Jasa',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
