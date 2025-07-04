<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;


class LaporanController extends Controller
{
    public function transaksi(Request $request)
    {
        try {
            $start = $request->query('start');
            $end = $request->query('end');
            $perPage = $request->query('per_page', 10);

            $query = DB::table('transaksi')
                ->leftJoin('staff', 'staff.id_staff', '=', 'transaksi.staff_id')
                ->leftJoin('pelanggan', 'pelanggan.id_pelanggan', '=', 'transaksi.pelanggan_id')
                ->select(
                    'staff.nama_staff',
                    'pelanggan.nama_pelanggan',
                    'transaksi.waktu_transaksi',
                    'transaksi.total_harga_transaksi'
                )
                ->when($start && $end, fn($q) => $q->whereBetween('transaksi.waktu_transaksi', [$start, $end]))
                ->orderByDesc('transaksi.waktu_transaksi');

            if ($perPage === 'all') {
                $data = $query->get();
                return response()->json([
                    'success' => true,
                    'message' => 'Data transaksi berhasil diambil',
                    'data' => $data,
                    'total' => $data->count()
                ]);
            }

            $data = $query->paginate((int) $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil diambil',
                'data' => $data,
                'total' => $data->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function produkTerlaris(Request $request)
    {
        try {
            $start = $request->query('start');
            $end = $request->query('end');
            $perPage = $request->query('per_page', 10);

            $query = DB::table('detail_transaksi')
                ->join('produk', 'produk.id_produk', '=', 'detail_transaksi.produk_id')
                ->join('transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi.transaksi_id')
                ->select(
                    'produk.nama_produk',
                    DB::raw('SUM(detail_transaksi.jumlah_produk_detail_transaksi) as total_terjual')
                )
                ->when($start && $end, fn($q) => $q->whereBetween('transaksi.waktu_transaksi', [$start, $end]))
                ->groupBy('produk.nama_produk')
                ->orderByDesc('total_terjual');

            if ($perPage === 'all') {
                $data = $query->get();
                return response()->json([
                    'success' => true,
                    'message' => 'Data produk terlaris berhasil diambil',
                    'data' => $data,
                    'total' => $data->count()
                ]);
            }

            $data = $query->paginate((int) $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data produk terlaris berhasil diambil',
                'data' => $data,
                'total' => $data->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk terlaris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function jasaTerlaris(Request $request)
    {
        try {
            $start = $request->query('start');
            $end = $request->query('end');
            $perPage = $request->query('per_page', 10);

            $query = DB::table('detail_transaksi_jasa')
                ->join('jasa', 'jasa.id_jasa', '=', 'detail_transaksi_jasa.jasa_id')
                ->join('transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi_jasa.transaksi_id')
                ->select(
                    'jasa.nama_jasa',
                    DB::raw('COUNT(*) as jumlah_digunakan')
                )
                ->when($start && $end, fn($q) => $q->whereBetween('transaksi.waktu_transaksi', [$start, $end]))
                ->groupBy('jasa.nama_jasa')
                ->orderByDesc('jumlah_digunakan');

            if ($perPage === 'all') {
                $data = $query->get();
                return response()->json([
                    'success' => true,
                    'message' => 'Data jasa terlaris berhasil diambil',
                    'data' => $data,
                    'total' => $data->count()
                ]);
            }

            $data = $query->paginate((int) $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data jasa terlaris berhasil diambil',
                'data' => $data,
                'total' => $data->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jasa terlaris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pengadaanProduk(Request $request)
    {
        try {
            $start = $request->query('start');
            $end = $request->query('end');
            $perPage = $request->query('per_page', 10);

            $query = DB::table('pengadaan_stok')
                ->join('supplier', 'supplier.id_supplier', '=', 'pengadaan_stok.supplier_id')
                ->join('staff', 'staff.id_staff', '=', 'pengadaan_stok.staff_id')
                ->select(
                    'staff.nama_staff',
                    'supplier.nama_supplier',
                    'pengadaan_stok.waktu_pengadaan_stok',
                    'pengadaan_stok.total_harga_pengadaan_stok'
                )
                ->when($start && $end, fn($q) => $q->whereBetween('pengadaan_stok.waktu_pengadaan_stok', [$start, $end]))
                ->orderByDesc('pengadaan_stok.waktu_pengadaan_stok');

            if ($perPage === 'all') {
                $data = $query->get();
                return response()->json([
                    'success' => true,
                    'message' => 'Data pengadaan produk berhasil diambil',
                    'data' => $data,
                    'total' => $data->count()
                ]);
            }

            $data = $query->paginate((int) $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data pengadaan produk berhasil diambil',
                'data' => $data,
                'total' => $data->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengadaan produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stokProduk(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);

            $query = DB::table('produk')
                ->select('nama_produk', 'stok_produk')
                ->orderBy('stok_produk', 'asc');

            if ($perPage === 'all') {
                $data = $query->get();
                return response()->json([
                    'success' => true,
                    'message' => 'Data stok produk berhasil diambil',
                    'data' => $data,
                    'total' => $data->count()
                ]);
            }

            $data = $query->paginate((int) $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data stok produk berhasil diambil',
                'data' => $data,
                'total' => $data->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data stok produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function keuangan(Request $request)
    {
        try {
            $start = $request->query('start');
            $end = $request->query('end');

            $pendapatan = DB::table('transaksi')
                ->when($start && $end, fn($q) => $q->whereBetween('waktu_transaksi', [$start, $end]))
                ->sum('total_harga_transaksi');

            $pengeluaran = DB::table('pengadaan_stok')
                ->when($start && $end, fn($q) => $q->whereBetween('waktu_pengadaan_stok', [$start, $end]))
                ->sum('total_harga_pengadaan_stok');

            $laba = $pendapatan - $pengeluaran;

            return response()->json([
                'success' => true,
                'message' => 'Data keuangan berhasil diambil',
                'data' => [
                    'pendapatan' => $pendapatan,
                    'pengeluaran' => $pengeluaran,
                    'laba_kotor' => $laba
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data keuangan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function opsiLaporan(Request $request)
    {
        try {
            $role = $request->user()->role->nama_role;

            // Semua opsi laporan yang tersedia
            $opsiSemua = [
                ['value' => 'transaksi',        'text' => 'Transaksi',       'tipe' => 'tabel'],
                ['value' => 'pengadaan-produk', 'text' => 'Pengadaan Stok',  'tipe' => 'tabel'],
                ['value' => 'produk-terlaris',  'text' => 'Produk Terlaris', 'tipe' => 'tabel'],
                ['value' => 'jasa-terlaris',    'text' => 'Jasa Terlaris',   'tipe' => 'tabel'],
                ['value' => 'stok-produk',      'text' => 'Stok Produk',     'tipe' => 'tabel'],
                ['value' => 'keuangan',         'text' => 'Laba Kotor',      'tipe' => 'ringkasan'],
            ];

            // Hak akses laporan berdasarkan role
            $aksesPerRole = [
                'Super Admin' => [
                    'transaksi',
                    'pengadaan-produk',
                    'produk-terlaris',
                    'jasa-terlaris',
                    'stok-produk',
                    'keuangan',
                ],
                'Admin' => [
                    'pengadaan-produk',
                    'produk-terlaris',
                    'jasa-terlaris',
                    'stok-produk',
                ],
                'Kasir' => [
                    'transaksi',
                ],
                'Pemilik' => [
                    'transaksi',
                    'pengadaan-produk',
                    'produk-terlaris',
                    'jasa-terlaris',
                    'stok-produk',
                    'keuangan',
                ],
            ];

            $opsiYangBoleh = collect($opsiSemua)
                ->filter(fn($item) => in_array($item['value'], $aksesPerRole[$role] ?? []))
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Opsi laporan berhasil diambil',
                'data' => $opsiYangBoleh,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil opsi laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function ambilDataLaporan(Request $request, $jenis)
    {
        try {
            $start = $request->start;
            $end = $request->end;

            switch ($jenis) {
                case 'transaksi':
                    return DB::table('transaksi')
                        ->leftJoin('staff', 'staff.id_staff', '=', 'transaksi.staff_id')
                        ->leftJoin('pelanggan', 'pelanggan.id_pelanggan', '=', 'transaksi.pelanggan_id')
                        ->select(
                            'staff.nama_staff',
                            'pelanggan.nama_pelanggan',
                            'transaksi.waktu_transaksi',
                            'transaksi.total_harga_transaksi'
                        )
                        ->when($start && $end, fn($q) => $q->whereBetween('transaksi.waktu_transaksi', [$start, $end]))
                        ->orderByDesc('transaksi.waktu_transaksi')
                        ->get();

                case 'pengadaan-produk':
                    return DB::table('pengadaan_stok')
                        ->join('supplier', 'supplier.id_supplier', '=', 'pengadaan_stok.supplier_id')
                        ->join('staff', 'staff.id_staff', '=', 'pengadaan_stok.staff_id')
                        ->select(
                            'staff.nama_staff',
                            'supplier.nama_supplier',
                            'pengadaan_stok.waktu_pengadaan_stok',
                            'pengadaan_stok.total_harga_pengadaan_stok'
                        )
                        ->when($start && $end, fn($q) => $q->whereBetween('pengadaan_stok.waktu_pengadaan_stok', [$start, $end]))
                        ->orderByDesc('pengadaan_stok.waktu_pengadaan_stok')
                        ->get();

                case 'produk-terlaris':
                    return DB::table('detail_transaksi')
                        ->join('produk', 'produk.id_produk', '=', 'detail_transaksi.produk_id')
                        ->join('transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi.transaksi_id')
                        ->select(
                            'produk.nama_produk',
                            DB::raw('SUM(detail_transaksi.jumlah_produk_detail_transaksi) as total_terjual')
                        )
                        ->when($start && $end, fn($q) => $q->whereBetween('transaksi.waktu_transaksi', [$start, $end]))
                        ->groupBy('produk.nama_produk')
                        ->orderByDesc('total_terjual')
                        ->get();

                case 'jasa-terlaris':
                    return DB::table('detail_transaksi_jasa')
                        ->join('jasa', 'jasa.id_jasa', '=', 'detail_transaksi_jasa.jasa_id')
                        ->join('transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi_jasa.transaksi_id')
                        ->select(
                            'jasa.nama_jasa',
                            DB::raw('COUNT(*) as jumlah_digunakan')
                        )
                        ->when($start && $end, fn($q) => $q->whereBetween('transaksi.waktu_transaksi', [$start, $end]))
                        ->groupBy('jasa.nama_jasa')
                        ->orderByDesc('jumlah_digunakan')
                        ->get();

                case 'stok-produk':
                    return DB::table('produk')
                        ->select('nama_produk', 'stok_produk')
                        ->orderBy('stok_produk', 'asc')
                        ->get();

                case 'keuangan':
                    $pendapatan = DB::table('transaksi')
                        ->when($start && $end, fn($q) => $q->whereBetween('waktu_transaksi', [$start, $end]))
                        ->sum('total_harga_transaksi');

                    $pengeluaran = DB::table('pengadaan_stok')
                        ->when($start && $end, fn($q) => $q->whereBetween('waktu_pengadaan_stok', [$start, $end]))
                        ->sum('total_harga_pengadaan_stok');

                    $laba = $pendapatan - $pengeluaran;

                    return collect([
                        [
                            'pendapatan' => $pendapatan,
                            'pengeluaran' => $pengeluaran,
                            'laba_kotor' => $laba
                        ]
                    ]);

                default:
                    return collect(); // kosong jika jenis tidak dikenali
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal ambil data Laporan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request, $jenis)
    {
        try {
            $data = $this->ambilDataLaporan($request, $jenis);
            $start = $request->query('start');
            $end = $request->query('end');

            return match ($jenis) {
                'transaksi' => Excel::download(new \App\Exports\TransaksiExport($data, $start, $end), 'laporan-transaksi.xlsx'),
                'pengadaan-produk' => Excel::download(new \App\Exports\PengadaanProdukExport($data, $start, $end), 'laporan-pengadaan-produk.xlsx'),
                'produk-terlaris' => Excel::download(new \App\Exports\ProdukTerlarisExport($data, $start, $end), 'laporan-produk-terlaris.xlsx'),
                'jasa-terlaris' => Excel::download(new \App\Exports\JasaTerlarisExport($data, $start, $end), 'laporan-jasa-terlaris.xlsx'),
                'stok-produk' => Excel::download(new \App\Exports\StokProdukExport($data, $start, $end), 'laporan-stok-produk.xlsx'),
                'keuangan' => Excel::download(new \App\Exports\KeuanganExport($data, $start, $end), 'laporan-keuangan.xlsx'),
                default => abort(404, 'Jenis laporan tidak dikenali'),
            };
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal export Excel data Laporan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    public function exportPdf(Request $request, $jenis)
    {
        try {
            $data = $this->ambilDataLaporan($request, $jenis);
            $start = $request->query('start');
            $end = $request->query('end');

            $viewName = match ($jenis) {
                'transaksi' => 'laporan.pdf.transaksi',
                'pengadaan-produk' => 'laporan.pdf.pengadaan-produk',
                'produk-terlaris' => 'laporan.pdf.produk-terlaris',
                'jasa-terlaris' => 'laporan.pdf.jasa-terlaris',
                'stok-produk' => 'laporan.pdf.stok-produk',
                'keuangan' => 'laporan.pdf.keuangan',
                default => null,
            };

            if (!$viewName || !view()->exists($viewName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'View PDF tidak ditemukan untuk jenis laporan ini.'
                ], 404);
            }


            $pdf = Pdf::loadView($viewName, compact('data', 'start', 'end'));
            return $pdf->download('laporan_' . $jenis . '.pdf');
        } catch (\Throwable $e) {
            Log::error("Gagal export PDF: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghasilkan PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
