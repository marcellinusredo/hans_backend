<?php

namespace App\Http\Controllers\Api;

use App\Models\Produk;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Models\PengadaanStok;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\DetailPengadaanStok;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PengadaanStokController extends Controller
{
    public function index(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            // Ambil parameter dari request dengan default jika tidak ada
            $perPage = $request->input('per_page', 5);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'waktu_pengadaan_stok');
            $sortDir = $request->input('sort_dir', 'desc');

            // Validasi kolom yang boleh untuk sorting agar aman dari injection
            $allowedSort = ['id_pengadaan_stok', 'supplier_id', 'waktu_pengadaan_stok', 'total_harga_pengadaan_stok', 'nomor_invoice_pengadaan_stok'];
            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'waktu_pengadaan_stok';
            }
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

            // Bangun query
            $query = PengadaanStok::with(['supplier:id_supplier,nama_supplier'])
                ->select('id_pengadaan_stok', 'supplier_id', 'staff_id', 'waktu_pengadaan_stok', 'total_harga_pengadaan_stok', 'nomor_invoice_pengadaan_stok');

            // Tambahkan filter pencarian jika ada
            if (!empty($search)) {
                $query->whereHas('supplier', function ($q) use ($search) {
                    $q->where('nama_supplier', 'like', '%' . $search . '%');
                });
            }

            // Tambahkan sorting
            $query->orderBy($sortBy, $sortDir);

            // Ambil data dengan pagination
            $pengadaan_stoks = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data hasil pagination
            $data = $pengadaan_stoks->getCollection()->map(function ($pengadaan_stok) {
                return [
                    'id_pengadaan_stok' => $pengadaan_stok->id_pengadaan_stok,
                    'supplier_id' => $pengadaan_stok->supplier_id,
                    'staff_id' => $pengadaan_stok->staff_id,
                    'nomor_invoice_pengadaan_stok' => $pengadaan_stok->nomor_invoice_pengadaan_stok,
                    'nama_supplier' => $pengadaan_stok->supplier->nama_supplier ?? '-',
                    'waktu_pengadaan_stok' => $pengadaan_stok->waktu_pengadaan_stok,
                    'total_harga_pengadaan_stok' => $pengadaan_stok->total_harga_pengadaan_stok,
                ];
            });

            // Kembalikan response JSON lengkap dengan info pagination
            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $pengadaan_stoks->currentPage(),
                    'last_page' => $pengadaan_stoks->lastPage(),
                    'per_page' => $pengadaan_stoks->perPage(),
                    'total' => $pengadaan_stoks->total(),
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Pengadaan Stok',
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
                'supplier_id' => 'required|exists:supplier,id_supplier',
                'staff_id' => 'required|exists:staff,id_staff',
                'waktu_pengadaan_stok' => 'required|date',
                'detail' => 'required|array|min:1',
                'detail.*.produk_id' => 'required|exists:produk,id_produk',
                'detail.*.jumlah_produk_detail_pengadaan_stok' => 'required|integer|min:1|max:2147483647',
                'detail.*.harga_produk_detail_pengadaan_stok' => 'required|numeric|min:1000|max:999999999999999999',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Hitung total harga pengadaan stok
            $totalHarga = 0;
            foreach ($request->detail as $detail) {
                $subtotal = $detail['jumlah_produk_detail_pengadaan_stok'] * $detail['harga_produk_detail_pengadaan_stok'];
                $totalHarga += $subtotal;
            }


            // Simpan data pengadaan stok dengan total harga yang dihitung
            $invoice = generateNomorInvoiceSafe('PGD');
            $pengadaan = PengadaanStok::create([
                'supplier_id' => $request->supplier_id,
                'staff_id' => $request->staff_id,
                'nomor_invoice_pengadaan_stok' => $invoice,
                'waktu_pengadaan_stok' => $request->waktu_pengadaan_stok,
                'total_harga_pengadaan_stok' => $totalHarga,
            ]);

            // Simpan detail pengadaan dan update stok produk
            foreach ($request->detail as $detail) {
                $subtotal = $detail['jumlah_produk_detail_pengadaan_stok'] * $detail['harga_produk_detail_pengadaan_stok'];
                DetailPengadaanStok::create([
                    'pengadaan_stok_id' => $pengadaan->id_pengadaan_stok,
                    'produk_id' => $detail['produk_id'],
                    'jumlah_produk_detail_pengadaan_stok' => $detail['jumlah_produk_detail_pengadaan_stok'],
                    'harga_produk_detail_pengadaan_stok' => $detail['harga_produk_detail_pengadaan_stok'],
                    'subtotal_produk_detail_pengadaan_stok' => $subtotal,
                ]);
                //Menambah jumlah stok
                Produk::tambahStok($detail['produk_id'], $detail['jumlah_produk_detail_pengadaan_stok']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pengadaan stok berhasil ditambahkan.',
                'total_harga' => $totalHarga,
                'id_pengadaan_stok' => $pengadaan->id_pengadaan_stok,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan pengadaan stok',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //cari data
            $pengadaan = PengadaanStok::with(['staff', 'detail_pengadaan_stok.produk'])->find($id);
            if (!$pengadaan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pengadaan stok tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $pengadaan
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Pengadaan Stok',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //validasi input
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:supplier,id_supplier',
                'staff_id' => 'required|exists:staff,id_staff',
                'waktu_pengadaan_stok' => 'required|date',
                'detail' => 'required|array|min:1',
                'detail.*.produk_id' => 'required|exists:produk,id_produk',
                'detail.*.jumlah_produk_detail_pengadaan_stok' => 'required|integer|min:1|max:2147483647',
                'detail.*.harga_produk_detail_pengadaan_stok' => 'required|numeric|min:1000|max:999999999999999999',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            //cari data
            $pengadaan = PengadaanStok::findOrFail($id);

            // Rollback stok lama
            foreach ($pengadaan->detail_pengadaan_stok as $oldDetail) {
                Produk::kurangiStok($oldDetail->produk_id, $oldDetail->jumlah_produk_detail_pengadaan_stok);
            }

            // Hapus semua detail lama
            $pengadaan->detail_pengadaan_stok()->delete();

            // buat ulang detail
            $totalHarga = 0;
            foreach ($request->detail as $dp) {
                $subtotal = $dp['jumlah_produk_detail_pengadaan_stok'] * $dp['harga_produk_detail_pengadaan_stok'];
                $totalHarga += $subtotal;

                DetailPengadaanStok::create([
                    'pengadaan_stok_id' => $pengadaan->id_pengadaan_stok,
                    'produk_id' => $dp['produk_id'],
                    'jumlah_produk_detail_pengadaan_stok' => $dp['jumlah_produk_detail_pengadaan_stok'],
                    'harga_produk_detail_pengadaan_stok' => $dp['harga_produk_detail_pengadaan_stok'],
                    'subtotal_produk_detail_pengadaan_stok' => $subtotal,
                ]);
                Produk::tambahStok($dp['produk_id'], $dp['jumlah_produk_detail_pengadaan_stok']);
            }

            // Update data utama
            $pengadaan->update([
                'supplier_id' => $request->supplier_id,
                'staff_id' => $request->staff_id,
                'waktu_pengadaan_stok' => $request->waktu_pengadaan_stok,
                'total_harga_pengadaan_stok' => $totalHarga,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pengadaan stok berhasil diperbarui.',
                'total_harga' => $totalHarga,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui pengadaan stok',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }




    public function destroy(string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            DB::beginTransaction();

            //cari data
            $pengadaan = PengadaanStok::with('detail_pengadaan_stok')->findOrFail($id);

            //kurangi stok produk
            foreach ($pengadaan->detail_pengadaan_stok as $detail) {
                Produk::kurangiStok($detail->produk_id, $detail->jumlah_produk_detail_pengadaan_stok);
            }

            //hapus data
            $pengadaan->detail_pengadaan_stok()->delete();
            $pengadaan->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data pengadaan stok berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data pengadaan stok.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDetail(Request $request, string $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            // Cek apakah pengadaan stok ada
            $pengadaan = PengadaanStok::find($id);
            if (!$pengadaan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengadaan stok tidak ditemukan.'
                ], 404);
            }

            // Query detail pengadaan langsung dari model
            $query = DetailPengadaanStok::with(['produk:id_produk,nama_produk'])
                ->where('pengadaan_stok_id', $id);
            //->select('id_detail_pengadaan_stok','produk_id','pengadaan_stok_id','harga_produk_detail_pengadaan_stok','jumlah_produk_detail_pengadaan_stok','subtotal_produk_detail_pengadaan_stok');

            // Search by nama_produk
            if ($request->has('search')) {
                $search = strtolower($request->search);
                $query->whereHas('produk', function ($q) use ($search) {
                    $q->whereRaw('LOWER(nama_produk) LIKE ?', ['%' . $search . '%']);
                });
            }

            // Sorting
            if ($request->has('sort_by') && $request->has('sort_order')) {
                $sortBy = $request->sort_by;
                $sortOrder = strtolower($request->sort_order) === 'desc' ? 'desc' : 'asc';

                if ($sortBy === 'nama_produk') {
                    $query->join('produk', 'detail_pengadaan_stok.produk_id', '=', 'produk.id_produk')
                        ->orderBy('produk.nama_produk', $sortOrder)
                        ->select('detail_pengadaan_stok.*'); // pastikan select ulang kolom utama
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }
            }

            // Pagination
            $perPage = intval($request->input('per_page', 10));
            $details = $query->paginate($perPage);

            return response()->json([
                'status' => true,
                'data' => $details->items(),
                'meta' => [
                    'total' => $details->total(),
                    'per_page' => $details->perPage(),
                    'current_page' => $details->currentPage(),
                    'last_page' => $details->lastPage(),
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Detail Pengadaan Stok',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function cetakInvoice($id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Admin']);

            //cari data
            $pengadaan = PengadaanStok::with(['staff', 'supplier', 'detail_pengadaan_stok.produk'])
                ->findOrFail($id);

            // Format tanggal & nama file
            $tanggal = Carbon::parse($pengadaan->tanggal_pengadaan_stok)->format('Y-m-d');
            $safeInvoiceNumber = Str::slug($pengadaan->nomor_invoice_pengadaan_stok);
            $fileName = "invoice-pengadaan-{$safeInvoiceNumber}.pdf";

            // Path penyimpanan relatif terhadap storage/app/public
            $folder = "invoice/pengadaan/{$tanggal}";
            $relativePath = "{$folder}/{$fileName}";


            $pdf = Pdf::loadView('invoice.pengadaan', compact('pengadaan'));
            Storage::disk('public')->put($relativePath, $pdf->output());

            // Kirim URL publik ke frontend
            return response()->json([
                'status' => true,
                'url' => asset("storage/{$relativePath}"),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal cetak data Pengadaan Stok',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
