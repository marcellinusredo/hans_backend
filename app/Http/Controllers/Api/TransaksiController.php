<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Jasa;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Models\DetailTransaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\DetailTransaksiJasa;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            // Ambil parameter dari request dengan default jika tidak ada
            $perPage = $request->input('per_page', 5);
            $page = $request->input('page', 1);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'waktu_transaksi');
            $sortDir = $request->input('sort_dir', 'desc');

            // Validasi kolom yang boleh untuk sorting agar aman dari injection
            $allowedSort = ['id_transaksi', 'pelanggan_id', 'staff_id', 'waktu_transaksi', 'total_harga_transaksi', 'nomor_invoice_transaksi'];
            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'waktu_transaksi';
            }
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

            // Bangun query
            $query = Transaksi::with(['pelanggan:id_pelanggan,nama_pelanggan'])
                ->select('id_transaksi', 'pelanggan_id', 'staff_id', 'waktu_transaksi', 'total_harga_transaksi', 'nomor_invoice_transaksi');

            // Tambahkan filter pencarian jika ada
            if (!empty($search)) {
                $query->whereHas('pelanggan', function ($q) use ($search) {
                    $q->where('nama_pelanggan', 'like', "%$search%");
                });
            }

            // Tambahkan sorting
            $query->orderBy($sortBy, $sortDir);

            // Ambil data dengan pagination
            $transaksis = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data hasil pagination
            $data = $transaksis->getCollection()->map(function ($transaksis) {
                return [
                    'id_transaksi' => $transaksis->id_transaksi,
                    'pelanggan_id' => $transaksis->pelanggan_id,
                    'staff_id' => $transaksis->staff_id,
                    'nomor_invoice_transaksi' => $transaksis->nomor_invoice_transaksi,
                    'nama_pelanggan' => $transaksis->pelanggan->nama_pelanggan ?? '-',
                    'waktu_transaksi' => $transaksis->waktu_transaksi,
                    'total_harga_transaksi' => $transaksis->total_harga_transaksi,
                ];
            });

            // Kembalikan response JSON lengkap dengan info pagination
            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $transaksis->currentPage(),
                    'last_page' => $transaksis->lastPage(),
                    'per_page' => $transaksis->perPage(),
                    'total' => $transaksis->total(),
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Transaksi',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            //validaei role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //validasi input
            $validator = Validator::make($request->all(), [
                'pelanggan_id' => 'required|exists:pelanggan,id_pelanggan',
                'staff_id' => 'required|exists:staff,id_staff',
                'waktu_transaksi' => 'required|date',
                'pembayaran_transaksi' => 'required|numeric|min:1|max:999999999999999999',
                //validasi detail produk
                'detail_produk' => 'nullable|array',
                'detail_produk.*.produk_id' => 'required|exists:produk,id_produk',
                'detail_produk.*.jumlah_produk_detail_transaksi' => 'required|numeric|min:1|max:2147483647',
                //validasi detail jasa
                'detail_jasa' => 'nullable|array',
                'detail_jasa.*.jasa_id' => 'required|exists:jasa,id_jasa',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validasi minimal harus ada satu produk atau jasa
            $detailProduk = $request->input('detail_produk', []);
            $detailJasa = $request->input('detail_jasa', []);

            if (empty($detailProduk) && empty($detailJasa)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaksi harus memiliki minimal satu detail produk atau jasa.',
                ], 422);
            }

            //Validasi stok produk
            if ($request->has('detail_produk')) {
                foreach ($request->detail_produk as $dp) {
                    $produk = Produk::findOrFail($dp['produk_id']);
                    if ($dp['jumlah_produk_detail_transaksi'] > $produk->stok_produk) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Jumlah produk ' . $produk->nama_produk . ' melebihi stok tersedia.'
                        ], 422);
                    }
                }
            }

            DB::beginTransaction();

            // Hitung Total harga transaksi
            $totalHarga = 0;
            if ($request->has('detail_produk')) {
                foreach ($request->detail_produk as $dp) {
                    $produk = Produk::findOrFail($dp['produk_id']);
                    $harga = $produk->harga_produk;
                    $subtotal = $dp['jumlah_produk_detail_transaksi'] * $harga;
                    $totalHarga += $subtotal;
                }
            }
            if ($request->has('detail_jasa')) {
                foreach ($request->detail_jasa as $dj) {
                    $jasa = Jasa::find($dj['jasa_id']);
                    $harga = $jasa->harga_jasa;
                    $totalHarga += $harga;
                }
            }

            //hitung kembalian
            $pembayaran = $request->pembayaran_transaksi;
            $kembalian = $pembayaran - $totalHarga;
            if ($pembayaran < $totalHarga) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jumlah pembayaran tidak mencukupi untuk total transaksi.',
                ], 422);
            }
            // Simpan data transaksi dengan total harga yang dihitung
            $invoice = generateNomorInvoiceSafe('TRX');
            $transaksi = Transaksi::create([
                'pelanggan_id' => $request->pelanggan_id,
                'staff_id' => $request->staff_id,
                'nomor_invoice_transaksi' => $invoice,
                'waktu_transaksi' => $request->waktu_transaksi,
                'total_harga_transaksi' => $totalHarga,
                'pembayaran_transaksi' => $pembayaran,
                'kembalian_transaksi' => $kembalian
            ]);

            // Simpan detail transaksi dan update stok produk
            if ($request->has('detail_produk')) {
                foreach ($request->detail_produk as $dp) {
                    $produk = Produk::findOrFail($dp['produk_id']);
                    $harga = $produk->harga_produk;
                    $subtotal = $dp['jumlah_produk_detail_transaksi'] * $harga;
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id_transaksi,
                        'produk_id' => $dp['produk_id'],
                        'jumlah_produk_detail_transaksi' => $dp['jumlah_produk_detail_transaksi'],
                        'harga_produk_detail_transaksi' => $harga,
                        'subtotal_produk_detail_transaksi' => $subtotal,
                    ]);
                    //Mengurangi jumlah stok
                    Produk::kurangiStok($dp['produk_id'], $dp['jumlah_produk_detail_transaksi']);
                }
            }

            // Simpan detail transaksi jasa
            if ($request->has('detail_jasa')) {
                foreach ($request->detail_jasa as $dj) {
                    $jasa = Jasa::findOrFail($dj['jasa_id']);
                    $harga = $jasa->harga_jasa;
                    DetailTransaksiJasa::create([
                        'transaksi_id' => $transaksi->id_transaksi,
                        'jasa_id' => $dj['jasa_id'],
                        'harga_jasa_detail_transaksi_jasa' => $harga,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Transaksi berhasil ditambahkan.',
                'total_harga' => $totalHarga,
                'kembalian' => $kembalian,
                'id_transaksi' => $transaksi->id_transaksi,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //cari data
            $transaksi = Transaksi::with(['pelanggan', 'detail_transaksi.produk', 'detail_transaksi_jasa.jasa'])->find($id);

            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $transaksi
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Transaksi',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //validsi input
            $validator = Validator::make($request->all(), [
                'pelanggan_id' => 'required|exists:pelanggan,id_pelanggan',
                'staff_id' => 'required|exists:staff,id_staff',
                'waktu_transaksi' => 'required|date',
                'pembayaran_transaksi' => 'required|numeric|min:1|max:999999999999999999',
                //validasi detail produk
                'detail_produk' => 'nullable|array',
                'detail_produk.*.produk_id' => 'required|exists:produk,id_produk',
                'detail_produk.*.jumlah_produk_detail_transaksi' => 'required|numeric|min:1|max:2147483647',
                //validasi detail jasa
                'detail_jasa' => 'nullable|array',
                'detail_jasa.*.jasa_id' => 'required|exists:jasa,id_jasa',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal validasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validasi manual: minimal harus ada satu produk atau jasa
            $detailProduk = $request->input('detail_produk', []);
            $detailJasa = $request->input('detail_jasa', []);

            if (empty($detailProduk) && empty($detailJasa)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaksi harus memiliki minimal satu detail produk atau jasa.',
                ], 422);
            }

            // Validasi stok baru saat update
            if ($request->has('detail_produk')) {
                $transaksiLama = Transaksi::with('detail_transaksi')->findOrFail($id); // gunakan eager load

                foreach ($request->detail_produk as $ndp) {
                    $produk = Produk::findOrFail($ndp['produk_id']);

                    // Ambil jumlah lama produk ini di transaksi (jika ada)
                    $jumlahLama = $transaksiLama->detail_transaksi
                        ->where('produk_id', $ndp['produk_id'])
                        ->first()
                        ->jumlah_produk_detail_transaksi ?? 0;

                    $stokSaatIni = $produk->stok_produk;
                    $stokTersedia = $stokSaatIni + $jumlahLama;

                    if ($ndp['jumlah_produk_detail_transaksi'] > $stokTersedia) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Jumlah produk ' . $produk->nama_produk . ' melebihi stok tersedia (' . $stokTersedia . ').'
                        ], 422);
                    }
                }
            }


            DB::beginTransaction();

            //cari data
            $transaksi = Transaksi::findOrFail($id);

            // Hitung Total harga transaksi
            $totalHarga = 0;
            if ($request->has('detail_produk')) {
                foreach ($request->detail_produk as $dp) {
                    $produk = Produk::findOrFail($dp['produk_id']);
                    $harga = $produk->harga_produk;
                    $subtotal = $dp['jumlah_produk_detail_transaksi'] * $harga;
                    $totalHarga += $subtotal;
                }
            }
            if ($request->has('detail_jasa')) {
                foreach ($request->detail_jasa as $dj) {
                    $jasa = Jasa::find($dj['jasa_id']);
                    $harga = $jasa->harga_jasa;
                    $totalHarga += $harga;
                }
            }


            //hitung kembalian
            $pembayaran = $request->pembayaran_transaksi;
            $kembalian = $pembayaran - $totalHarga;
            if ($pembayaran < $totalHarga) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jumlah pembayaran tidak mencukupi untuk total transaksi.',
                ], 422);
            }

            // Rollback stok lama
            if ($request->has('detail_produk')) {
                foreach ($transaksi->detail_transaksi as $odp) {
                    Produk::tambahStok($odp->produk_id, $odp->jumlah_produk_detail_transaksi);
                }
            }

            // Hapus semua detail lama
            $transaksi->detail_transaksi()->delete();
            $transaksi->detail_transaksi_jasa()->delete();


            // Proses produk
            if ($request->has('detail_produk')) {
                foreach ($request->detail_produk as $dp) {
                    $produk = Produk::findOrFail($dp['produk_id']);
                    $harga = $produk->harga_produk;
                    $subtotal = $dp['jumlah_produk_detail_transaksi'] * $harga;
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id_transaksi,
                        'produk_id' => $dp['produk_id'],
                        'jumlah_produk_detail_transaksi' => $dp['jumlah_produk_detail_transaksi'],
                        'harga_produk_detail_transaksi' => $harga,
                        'subtotal_produk_detail_transaksi' => $subtotal,
                    ]);
                    Produk::kurangiStok($dp['produk_id'], $dp['jumlah_produk_detail_transaksi']);
                }
            }


            // Proses jasa
            if ($request->has('detail_jasa')) {
                foreach ($request->detail_jasa as $dj) {
                    $jasa = Jasa::findOrFail($dj['jasa_id']);
                    $harga = $jasa->harga_jasa;
                    DetailTransaksiJasa::create([
                        'transaksi_id' => $transaksi->id_transaksi,
                        'jasa_id' => $dj['jasa_id'],
                        'harga_jasa_detail_transaksi_jasa' => $harga,
                    ]);
                }
            }

            // Update transaksi utama
            $transaksi->update([
                'pelanggan_id' => $request->pelanggan_id,
                'staff_id' => $request->staff_id,
                'waktu_transaksi' => $request->waktu_transaksi,
                'total_harga_transaksi' => $totalHarga,
                'pembayaran_transaksi' => $pembayaran,
                'kembalian_transaksi' => $kembalian,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Transaksi berhasil diperbarui.',
                'total_harga' => $totalHarga,
                'kembalian' => $kembalian,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui transaksi',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            DB::beginTransaction();

            //cari data
            $transaksi = Transaksi::with('detail_transaksi')->find($id);

            //tambah stok produk
            foreach ($transaksi->detail_transaksi as $detail) {
                Produk::tambahStok($detail->produk_id, $detail->jumlah_produk_detail_transaksi);
            }

            // Hapus semua detail lama
            $transaksi->detail_transaksi()->delete();
            $transaksi->detail_transaksi_jasa()->delete();
            $transaksi->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data pengadaan transaksi berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data pengadaan transaksi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showDetailTransaksi(Request $request, $id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            // Ambil parameter dari query string
            $search = $request->input('search');
            $sortBy = $request->input('sortBy', 'nama'); // default: nama
            $sortDesc = filter_var($request->input('sortDesc'), FILTER_VALIDATE_BOOLEAN); // true / false
            $perPage = $request->input('perPage', 10); // default 10
            $page = $request->input('page', 1);

            // Ambil detail produk
            $detailProduk = DetailTransaksi::with('produk')
                ->where('transaksi_id', $id)
                ->get()
                ->map(function ($item) {
                    return [
                        'transaksi_id' => $item->transaksi_id,
                        'id_produk' => $item->produk->id_produk,
                        'id_jasa' => null,
                        'nama' => $item->produk->nama_produk,
                        'jenis' => 'Produk',
                        'kode' => $item->produk->kode_produk,
                        'harga' => $item->harga_produk_detail_transaksi,
                        'jumlah' => $item->jumlah_produk_detail_transaksi,
                        'subtotal' => $item->subtotal_produk_detail_transaksi,
                    ];
                });

            // Ambil detail jasa
            $detailJasa = DetailTransaksiJasa::with('jasa')
                ->where('transaksi_id', $id)
                ->get()
                ->map(function ($item) {
                    return [
                        'transaksi_id' => $item->transaksi_id,
                        'id_produk' => null,
                        'id_jasa' => $item->jasa->id_jasa,
                        'nama' => $item->jasa->nama_jasa,
                        'jenis' => 'Jasa',
                        'kode' => '-',
                        'harga' => $item->harga_jasa_detail_transaksi_jasa,
                        'jumlah' => 1,
                        'subtotal' => $item->harga_jasa_detail_transaksi_jasa,
                    ];
                });

            // Gabung data
            $combined = $detailProduk->concat($detailJasa);

            // Filter pencarian
            if ($search) {
                $combined = $combined->filter(function ($item) use ($search) {
                    return stripos($item['nama'], $search) !== false ||
                        stripos($item['jenis'], $search) !== false ||
                        stripos($item['kode'], $search) !== false;
                });
            }

            // Sorting
            $combined = $combined->sortBy([
                [$sortBy, $sortDesc ? 'desc' : 'asc']
            ])->values();

            // Pagination manual
            $total = $combined->count();
            $paginated = $combined->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi ditemukan',
                'data' => $paginated,
                'pagination' => [
                    'total' => $total,
                    'per_page' => (int)$perPage,
                    'current_page' => (int)$page,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Detail Transaksi',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function cetakInvoice($id)
    {
        try {
            //validasi role
            RoleHelper::allowOnly(['Super Admin', 'Kasir']);

            //cari data
            $transaksi = Transaksi::with([
                'staff',
                'detail_transaksi.produk',
                'detail_transaksi_jasa.jasa'
            ])->findOrFail($id);

            //nama file
            $safeInvoiceNumber = Str::slug($transaksi->nomor_invoice_transaksi);
            $fileName = "invoice-transaksi-{$safeInvoiceNumber}.pdf";

            //stream pdf
            $pdf = Pdf::loadView('invoice.transaksi', compact('transaksi'));
            return $pdf->stream($fileName);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal cetak data Transaksi',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
