<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use App\Helpers\RoleHelper;
use Mockery\Matcher\Subset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    ///GET /Menampilkan semua data
    public function index(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            // Ambil parameter dari request dengan default jika tidak ada
            $perPage = $request->input('per_page', 5);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'nama_supplier');
            $sortDir = $request->input('sort_dir', 'asc');

            // Validasi kolom yang boleh untuk sorting agar aman dari injection
            $allowedSort = ['id_supplier', 'nama_supplier'];
            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'nama_supplier';
            }
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

            // Bangun query 
            $query = Supplier::select('id_supplier', 'nama_supplier', 'nomor_telp_supplier', 'alamat_supplier');

            // Tambahkan filter pencarian jika ada
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_supplier', 'like', '%' . $search . '%');
                });
            }

            // Tambahkan sorting
            $query->orderBy($sortBy, $sortDir);

            // Ambil data dengan pagination
            $supliers = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data hasil pagination
            $data = $supliers->getCollection()->map(function ($supplier) {
                return [
                    'id_supplier' => $supplier->id_supplier,
                    'nama_supplier' => $supplier->nama_supplier,
                    'nomor_telp_supplier' => $supplier->nomor_telp_supplier,
                    'alamat_supplier' => $supplier->alamat_supplier,
                ];
            });

            // Kembalikan response JSON lengkap dengan info pagination
            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $supliers->currentPage(),
                    'last_page' => $supliers->lastPage(),
                    'per_page' => $supliers->perPage(),
                    'total' => $supliers->total(),
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Supplier',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //POST /Menambah Data
    public function store(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //validasi input
            $validator = Validator::make($request->all(), [
                'nama_supplier' => 'required|string|max:255',
                'nomor_telp_supplier' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
                'alamat_supplier' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //buat data
            $supplier = Supplier::create([
                'nama_supplier' => $request->nama_supplier,
                'nomor_telp_supplier' => $request->nomor_telp_supplier,
                'alamat_supplier' => $request->alamat_supplier,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Supplier berhasil ditambahkan',
                'data' => $supplier
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal buat data Supplier',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //GET /Menampilkan data tertentu
    public function show(string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            // Ambil data supplier berdasarkan id
            $supplier = Supplier::select('id_supplier', 'nama_supplier', 'nomor_telp_supplier', 'alamat_supplier')
                ->where('id_supplier', $id)
                ->first();

            // Jika data tidak ditemukan
            if (!$supplier) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data supplier tidak ditemukan'
                ], 404);
            }

            // Kirim data tanpa id_supplier
            return response()->json([
                'status' => true,
                'data' => $supplier
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Supplier',
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

            //cari data
            $supplier = Supplier::find($id);
            if (!$supplier) {
                return response()->json(['status' => false, 'message' => 'Supplier tidak ditemukan'], 404);
            }

            //validasi input
            $validator = Validator::make($request->all(), [
                'nama_supplier' => 'required|string|max:255',
                'nomor_telp_supplier' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
                'alamat_supplier' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //update data
            $supplier->update([
                'nama_supplier' => $request->nama_supplier,
                'nomor_telp_supplier' => $request->nomor_telp_supplier,
                'alamat_supplier' => $request->alamat_supplier,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Supplier berhasil diupdate',
                'data' => $supplier
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update data Supplier',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //DELETE / Hapus data tertentu
    public function destroy(string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //cari data
            $supplier = Supplier::find($id);
            if (!$supplier) {
                return response()->json(['status' => false, 'message' => 'Supplier tidak ditemukan'], 404);
            }

            //hapus data
            $supplier->delete();

            return response()->json([
                'status' => true,
                'message' => 'Supplier berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus data Supplier',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    public function getDropdown()
    {
        try {
            // Validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            // Ambil hanya kolom id_supplier dan nama_supplier
            $data = Supplier::select('id_supplier', 'nama_supplier')->get();

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Supplier',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
