<?php

namespace App\Http\Controllers\Api;

use App\Models\Staff;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    ///GET /Menampilkan semua data
    public function index(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Pemilik']);
            
            // Ambil parameter dari request dengan default jika tidak ada
            $perPage = $request->input('per_page', 5);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'nama_staff');
            $sortDir = $request->input('sort_dir', 'asc');

            // Validasi kolom yang boleh untuk sorting agar aman dari injection
            $allowedSort = ['id_staff', 'nama_staff', 'username_staff', 'role_id'];
            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'nama_staff';
            }
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

            // Bangun query
            $query = Staff::with(['role:id_role,nama_role'])
                ->select('id_staff', 'role_id', 'nama_staff', 'nomor_telp_staff', 'alamat_staff', 'username_staff');

            // Tambahkan filter pencarian jika ada
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_staff', 'like', '%' . $search . '%')
                        ->orWhere('username_staff', 'like', '%' . $search . '%')
                        ->orWhereHas('role', function ($qr) use ($search) {
                            $qr->where('nama_role', 'like', '%' . $search . '%');
                        });
                });
            }

            // Tambahkan sorting
            $query->orderBy($sortBy, $sortDir);

            // Ambil data dengan pagination
            $staffs = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data hasil pagination
            $data = $staffs->getCollection()->map(function ($staff) {
                return [
                    'id_staff' => $staff->id_staff,
                    'role_id' => $staff->role_id,
                    'nama_staff' => $staff->nama_staff,
                    'nomor_telp_staff' => $staff->nomor_telp_staff,
                    'alamat_staff' => $staff->alamat_staff,
                    'username_staff' => $staff->username_staff,
                    'nama_role' => $staff->role->nama_role ?? '-',
                ];
            });

            // Kembalikan response JSON lengkap dengan info pagination
            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $staffs->currentPage(),
                    'last_page' => $staffs->lastPage(),
                    'per_page' => $staffs->perPage(),
                    'total' => $staffs->total(),
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Staff',
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
                'role_id' => 'required|exists:role,id_role',
                'nama_staff' => 'required|string|max:255',
                'nomor_telp_staff' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
                'alamat_staff' => 'nullable|string',
                'username_staff' => 'required|string|unique:staff,username_staff',
                'password_staff' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //buat data
            $staff = Staff::create([
                'role_id' => $request->role_id,
                'nama_staff' => $request->nama_staff,
                'nomor_telp_staff' => $request->nomor_telp_staff,
                'alamat_staff' => $request->alamat_staff,
                'username_staff' => $request->username_staff,
                'password_staff' =>  Hash::make($request->password_staff),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Staff berhasil ditambahkan',
                'data' => $staff
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal buat data Staff',
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
            $staff = Staff::with(['role:id_role,nama_role'])
                ->select('id_staff', 'role_id', 'nama_staff', 'nomor_telp_staff', 'alamat_staff', 'username_staff')
                ->where('id_staff', $id)
                ->first();

            if (!$staff) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data staff tidak ditemukan'
                ], 404);
            }

            // Format responsenya
            return response()->json([
                'status' => true,
                'data' => [
                    'id_staff' => $staff->id_staff,
                    'role_id' => $staff->role_id,
                    'nama_staff' => $staff->nama_staff,
                    'nomor_telp_staff' => $staff->nomor_telp_staff,
                    'alamat_staff' => $staff->alamat_staff,
                    'username_staff' => $staff->username_staff,
                    'nama_role' => $staff->role->nama_role,
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Staff',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    //PUT / Update data tertentu
    public function update(Request $request, string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Pemilik']);

            //cari data
            $staff = Staff::find($id);
            if (!$staff) {
                return response()->json(['
                status' => false, 
                'message' => 'Staff tidak ditemukan'
            ], 404);
            }

            //validasi input
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|exists:role,id_role',
                'nama_staff' => 'required|string|max:255',
                'nomor_telp_staff' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
                'alamat_staff' => 'nullable|string',
                'username_staff' => 'required|string|max:255|unique:staff,username_staff,' . $id . ',id_staff',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //update data
            $updateData = [
                'role_id' => $request->role_id,
                'nama_staff' => $request->nama_staff,
                'nomor_telp_staff' => $request->nomor_telp_staff,
                'alamat_staff' => $request->alamat_staff,
                'username_staff' => $request->username_staff,
            ];

            // Hanya update password jika diisi
            if (!empty($request->password_staff)) {
                $updateData['password_staff'] = Hash::make($request->password_staff);
            }

            //update data ke database
            $staff->update($updateData);

            return response()->json([
                'status' => true,
                'message' => 'Staff berhasil diupdate',
                'data' => $staff
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update data Staff',
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
            $staff = Staff::find($id);
            if (!$staff) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staff tidak ditemukan'
                ], 404);
            }

            //hapus data
            $staff->delete();

            return response()->json([
                'status' => true,
                'message' => 'Staff berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Staff',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
