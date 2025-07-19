<?php

namespace App\Http\Controllers\Api;

use App\Models\Pelanggan;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PelangganController extends Controller
{
    //GET /Menampilkan semua data
    public function index(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            // Ambil parameter dari request dengan default jika tidak ada
            $perPage = $request->input('per_page', 5);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'nama_pelanggan');
            $sortDir = $request->input('sort_dir', 'asc');

            // Validasi kolom yang boleh untuk sorting agar aman dari injection
            $allowedSort = ['id_pelanggan', 'nama_pelanggan'];
            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'nama_pelanggan';
            }
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

            // Bangun query
            $query = Pelanggan::select('id_pelanggan', 'nama_pelanggan', 'alamat_pelanggan', 'nomor_telp_pelanggan');

            // Tambahkan filter pencarian jika ada
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                });
            }

            // Tambahkan sorting
            $query->orderBy($sortBy, $sortDir);

            // Ambil data dengan pagination
            $pelanggans = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data hasil pagination
            $data = $pelanggans->getCollection()->map(function ($pelanggan) {
                return [
                    'id_pelanggan' => $pelanggan->id_pelanggan,
                    'nama_pelanggan' => $pelanggan->nama_pelanggan,
                    'alamat_pelanggan' => $pelanggan->alamat_pelanggan,
                    'nomor_telp_pelanggan' => $pelanggan->nomor_telp_pelanggan,
                ];
            });

            // Kembalikan response JSON lengkap dengan info pagination
            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $pelanggans->currentPage(),
                    'last_page' => $pelanggans->lastPage(),
                    'per_page' => $pelanggans->perPage(),
                    'total' => $pelanggans->total(),
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Pelanggan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //POST /Menambah Data
    public function store(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //validasi input
            $validator = Validator::make($request->all(), [
                'nama_pelanggan' => 'required|string|min:1|max:25',
                'alamat_pelanggan' => 'nullable|string|max:50',
                'nomor_telp_pelanggan' => ['nullable', 'regex:/^0[0-9]{9,14}$/'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //buat data
            $pelanggan = Pelanggan::create([
                'nama_pelanggan' => $request->nama_pelanggan,
                'alamat_pelanggan' => $request->alamat_pelanggan,
                'nomor_telp_pelanggan' => $request->nomor_telp_pelanggan,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Pelanggan berhasil ditambahkan',
                'data' => $pelanggan
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal buat data Pelanggan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //GET /Menampilkan data tertentu
    public function show(string $id)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //Cari data
            $pelanggan = Pelanggan::select('id_pelanggan', 'nama_pelanggan', 'alamat_pelanggan', 'nomor_telp_pelanggan')
                ->where('id_pelanggan', $id)
                ->first();

            if (!$pelanggan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pelanggan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $pelanggan
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Pelanggan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //PUT / Update data tertentu
    public function update(Request $request, string $id)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //Cari data
            $pelanggan = Pelanggan::find($id);
            if (!$pelanggan) {
                return response()->json(['status' => false, 'message' => 'Pelanggan tidak ditemukan'], 404);
            }

            //Validator input
            $validator = Validator::make($request->all(), [
                'nama_pelanggan' => 'required|string|min:1|max:25',
                'alamat_pelanggan' => 'nullable|string|max:50',
                'nomor_telp_pelanggan' => ['nullable', 'regex:/^0[0-9]{9,14}$/'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //Update data
            $pelanggan->update([
                'nama_pelanggan' => $request->nama_pelanggan,
                'alamat_pelanggan' => $request->alamat_pelanggan,
                'nomor_telp_pelanggan' => $request->nomor_telp_pelanggan,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Pelanggan berhasil diupdate',
                'data' => $pelanggan
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update data Pelanggan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //DELETE / Hapus data tertentu
    public function destroy(string $id)
    {
        try {
            //Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //Cari data
            $pelanggan = Pelanggan::find($id);
            if (!$pelanggan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pelanggan tidak ditemukan'
                ], 404);
            }

            //Hapus data
            $pelanggan->delete();

            return response()->json([
                'status' => true,
                'message' => 'Pelanggan berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus data Pelanggan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function getDropdown()
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //Filter data
            $data = Pelanggan::select('id_pelanggan', 'nama_pelanggan')->get();

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Pelanggan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
