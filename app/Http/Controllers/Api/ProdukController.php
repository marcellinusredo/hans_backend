<?php

namespace App\Http\Controllers\Api;

use App\Models\Produk;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin', 'Kasir']);

            // Ambil parameter dari request dengan default jika tidak ada
            $perPage = $request->input('per_page', 5);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'nama_produk');
            $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            // Validasi kolom yang boleh untuk sorting agar aman dari injection
            $allowedSort = ['id_produk', 'kategori_id', 'nama_produk', 'kode_produk', 'stok_produk', 'harga_produk'];
            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'nama_produk';
            }

            // Bangun query
            $query = Produk::with(['kategori:id_kategori,nama_kategori'])
                ->select('id_produk', 'kategori_id', 'nama_produk', 'harga_produk', 'stok_produk', 'deskripsi_produk', 'gambar_produk', 'kode_produk');

            // Tambahkan filter pencarian jika ada
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_produk', 'like', '%' . $search . '%');
                });
            }

            // Tambahkan sorting
            $query->orderBy($sortBy, $sortDir);

            // Ambil data dengan pagination
            $produks = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data hasil pagination
            $data = $produks->getCollection()->map(function ($produk) {
                return [
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => $produk->nama_produk,
                    'kode_produk' => $produk->kode_produk,
                    'kategori_id' => $produk->kategori_id,
                    'nama_kategori' => $produk->kategori->nama_kategori ?? '-',
                    'harga_produk' => $produk->harga_produk,
                    'stok_produk' => $produk->stok_produk,
                    'deskripsi_produk' => $produk->deskripsi_produk,
                    'gambar_produk' => $produk->gambar_produk ? asset('storage/' . $produk->gambar_produk) : null,
                ];
            });

            // Kembalikan response JSON lengkap dengan info pagination
            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $produks->currentPage(),
                    'last_page' => $produks->lastPage(),
                    'per_page' => $produks->perPage(),
                    'total' => $produks->total()
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //validasi input
            $validator = Validator::make($request->all(), [
                'kategori_id' => 'required|exists:kategori,id_kategori',
                'nama_produk' => 'required|string|max:255',
                'kode_produk' => 'required|string|max:255|unique:produk,kode_produk',
                'harga_produk' => 'required|numeric|min:0',
                'deskripsi_produk' => 'nullable|string',
                'gambar_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //cek gambar
            $gambarPath = null;
            if ($request->hasFile('gambar_produk')) {
                $gambarPath = $request->file('gambar_produk')->store('produk', 'public');
            }

            //buat data
            $produk = Produk::create([
                'kategori_id' => $request->kategori_id,
                'nama_produk' => $request->nama_produk,
                'kode_produk' => $request->kode_produk,
                'harga_produk' => $request->harga_produk,
                'stok_produk' => 0,
                'deskripsi_produk' => $request->deskripsi_produk,
                'gambar_produk' => $gambarPath
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Produk berhasil ditambahkan',
                'data' => $produk
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal buat data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin', 'Kasir']);

            //cari data
            $produk = Produk::with(['kategori:id_kategori,nama_kategori'])
                ->select('id_produk', 'kategori_id', 'nama_produk', 'harga_produk', 'stok_produk', 'deskripsi_produk', 'gambar_produk', 'kode_produk')
                ->where('id_produk', $id)
                ->first();

            if (!$produk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data produk tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => $produk->nama_produk,
                    'kode_produk' => $produk->kode_produk,
                    'kategori_id' => $produk->kategori_id,
                    'nama_kategori' => $produk->kategori->nama_kategori ?? '-',
                    'harga_produk' => $produk->harga_produk,
                    'stok_produk' => $produk->stok_produk,
                    'deskripsi_produk' => $produk->deskripsi_produk,
                    'gambar_produk' => $produk->gambar_produk ? asset('storage/' . $produk->gambar_produk) : null
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //cari data
            $produk = Produk::find($id);
            if (!$produk) {
                return response()->json(['status' => false, 'message' => 'Produk tidak ditemukan'], 404);
            }

            //validasi input
            $validator = Validator::make($request->all(), [
                'kategori_id' => 'required|exists:kategori,id_kategori',
                'nama_produk' => 'required|string|max:255',
                'kode_produk' => 'required|string|max:255|unique:produk,kode_produk,' . $id . ',id_produk',
                'harga_produk' => 'required|numeric|min:0',
                'deskripsi_produk' => 'nullable|string',
                'gambar_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            //cek input gambar
            $gantiGambar = $request->input('ganti_gambar') == 'true';
            $hapusGambar = $request->input('hapus_gambar') == 'true';

            // Validasi: jika ganti gambar tapi tidak ada file yang dikirim
            if ($gantiGambar && !$request->hasFile('gambar_produk')) {
                return response()->json([
                    'message' => 'Gambar baru wajib diunggah saat memilih ganti gambar.'
                ], 422);
            }

            // Validasi: jika hapus gambar tapi produk memang tidak punya gambar
            if ($hapusGambar && empty($produk->gambar_produk)) {
                return response()->json([
                    'message' => 'Produk tidak memiliki gambar yang bisa dihapus.'
                ], 422);
            }

            // Proses gambar berdasarkan aksi
            if ($gantiGambar && $request->hasFile('gambar_produk')) {
                // Hapus gambar lama jika ada
                if ($produk->gambar_produk && Storage::disk('public')->exists($produk->gambar_produk)) {
                    Storage::disk('public')->delete($produk->gambar_produk);
                }
                // Simpan gambar baru
                $produk->gambar_produk = $request->file('gambar_produk')->store('produk', 'public');
            } elseif ($hapusGambar) {
                if ($produk->gambar_produk && Storage::disk('public')->exists($produk->gambar_produk)) {
                    Storage::disk('public')->delete($produk->gambar_produk);
                }
                $produk->gambar_produk = null;
            }

            //update data
            $produk->update([
                'kategori_id' => $request->kategori_id,
                'nama_produk' => $request->nama_produk,
                'kode_produk' => $request->kode_produk,
                'harga_produk' => $request->harga_produk,
                'deskripsi_produk' => $request->deskripsi_produk,
                'gambar_produk' => $produk->gambar_produk
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Produk berhasil diupdate',
                'data' => $produk
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //cari data
            $produk = Produk::find($id);
            if (!$produk) {
                return response()->json([
                    'status' => false,
                    'message' =>
                    'Produk tidak ditemukan'
                ], 404);
            }

            //hapus gambar jika ada
            if ($produk->gambar_produk && Storage::disk('public')->exists($produk->gambar_produk)) {
                Storage::disk('public')->delete($produk->gambar_produk);
            }

            //hapus data
            $produk->delete();

            return response()->json([
                'status' => true,
                'message' => 'Produk berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function getDropdownTransaksi()
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin', 'Kasir']);

            //filter data
            $produk = Produk::select('id_produk', 'nama_produk', 'stok_produk', 'harga_produk')
                ->where('stok_produk', '>=', 1)
                ->get();

            return response()->json([
                'status' => true,
                'data' => $produk
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Produk',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    public function getDropdown()
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //filter data
            $produk = Produk::select('id_produk', 'nama_produk', 'stok_produk', 'harga_produk')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $produk
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
