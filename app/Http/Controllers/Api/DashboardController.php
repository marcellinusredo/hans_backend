<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\PengadaanStok;
use App\Models\Produk;
use App\Models\Jasa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            //Validasi role
            $user = Auth::user();
            if (!$user || !$user->role) {
                return response()->json([
                    'status' => false,
                    'message' => 'Role tidak ditemukan atau user belum login'
                ], 403);
            }

            //Ambil bulan dan tahun
            $bulan_ini = Carbon::now()->month;
            $tahun_ini = Carbon::now()->year;

            // Pendapatan bulan ini
            $pendapatan = Transaksi::whereMonth('waktu_transaksi', $bulan_ini)
                ->whereYear('waktu_transaksi', $tahun_ini)
                ->sum('total_harga_transaksi');

            // Pengeluaran pengadaan bulan ini
            $pengeluaran_pengadaan = PengadaanStok::whereMonth('waktu_pengadaan_stok', $bulan_ini)
                ->whereYear('waktu_pengadaan_stok', $tahun_ini)
                ->sum('total_harga_pengadaan_stok');

            // Jumlah transaksi bulan ini
            $jumlah_transaksi = Transaksi::whereMonth('waktu_transaksi', $bulan_ini)
                ->whereYear('waktu_transaksi', $tahun_ini)
                ->count();

            // Jumlah pengadaan bulan ini
            $jumlah_pengadaan = PengadaanStok::whereMonth('waktu_pengadaan_stok', $bulan_ini)
                ->whereYear('waktu_pengadaan_stok', $tahun_ini)
                ->count();

            // Transaksi terbaru
            $transaksi_terbaru = Transaksi::with('pelanggan')
                ->latest('waktu_transaksi')
                ->take(5)
                ->get();

            // Pengadaan terbaru
            $pengadaan_terbaru = PengadaanStok::with('supplier')
                ->latest('waktu_pengadaan_stok')
                ->take(5)
                ->get();

            // Jasa terlaris
            $jasa_terlaris = DB::table('detail_transaksi_jasa')
                ->join('jasa', 'jasa.id_jasa', '=', 'detail_transaksi_jasa.jasa_id')
                ->join('transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi_jasa.transaksi_id')
                ->select('jasa.nama_jasa', DB::raw('COUNT(*) as total'))
                ->whereMonth('transaksi.waktu_transaksi', $bulan_ini)
                ->whereYear('transaksi.waktu_transaksi', $tahun_ini)
                ->groupBy('jasa.id_jasa', 'jasa.nama_jasa')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            // Produk terlaris
            $produk_terlaris = DB::table('detail_transaksi')
                ->join('produk', 'produk.id_produk', '=', 'detail_transaksi.produk_id')
                ->join('transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi.transaksi_id')
                ->select('produk.nama_produk', DB::raw('SUM(jumlah_produk_detail_transaksi) as total'))
                ->whereMonth('transaksi.waktu_transaksi', $bulan_ini)
                ->whereYear('transaksi.waktu_transaksi', $tahun_ini)
                ->groupBy('produk.id_produk', 'produk.nama_produk')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            // Produk hampir habis
            $jumlah_produk_hampir_habis = Produk::where('stok_produk', '<=', 5)->count();
            $produk_hampir_habis = Produk::where('stok_produk', '<=', 5)
                ->get(['id_produk', 'nama_produk', 'stok_produk']);

            // Respons sesuai role
            $role = $user->role->nama_role;
            switch (strtolower($role)) {
                case 'kasir':
                    return response()->json([
                        'pendapatan_bulan_ini' => $pendapatan,
                        'jumlah_transaksi_bulan_ini' => $jumlah_transaksi,
                        'transaksi_terbaru' => $transaksi_terbaru,
                        'produk_terlaris' => $produk_terlaris,
                        'jasa_terlaris' => $jasa_terlaris,
                    ]);

                case 'admin':
                    return response()->json([
                        'pengeluaran_pengadaan_bulan_ini' => $pengeluaran_pengadaan,
                        'jumlah_pengadaan_bulan_ini' => $jumlah_pengadaan,
                        'jumlah_produk_hampir_habis' => $jumlah_produk_hampir_habis,
                        'produk_hampir_habis' => $produk_hampir_habis,
                        'produk_terlaris' => $produk_terlaris,
                        'jasa_terlaris' => $jasa_terlaris,
                        'pengadaan_terbaru' => $pengadaan_terbaru,
                    ]);

                case 'pemilik':
                    return response()->json([
                        'pendapatan_bulan_ini' => $pendapatan,
                        'pengeluaran_pengadaan_bulan_ini' => $pengeluaran_pengadaan,
                        'jumlah_transaksi_bulan_ini' => $jumlah_transaksi,
                        'jumlah_pengadaan_bulan_ini' => $jumlah_pengadaan,
                        'produk_terlaris' => $produk_terlaris,
                        'jasa_terlaris' => $jasa_terlaris,
                        'jumlah_produk_hampir_habis' => $jumlah_produk_hampir_habis,
                        'produk_hampir_habis' => $produk_hampir_habis,
                    ]);

                case 'super admin':
                default:
                    return response()->json([
                        'pendapatan_bulan_ini' => $pendapatan,
                        'pengeluaran_pengadaan_bulan_ini' => $pengeluaran_pengadaan,
                        'jumlah_transaksi_bulan_ini' => $jumlah_transaksi,
                        'jumlah_pengadaan_bulan_ini' => $jumlah_pengadaan,
                        'jumlah_produk_hampir_habis' => $jumlah_produk_hampir_habis,
                        'produk_hampir_habis' => $produk_hampir_habis,
                        'transaksi_terbaru' => $transaksi_terbaru,
                        'jasa_terlaris' => $jasa_terlaris,
                        'produk_terlaris' => $produk_terlaris,
                        'pengadaan_terbaru' => $pengadaan_terbaru,
                    ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memuat data Dashbord',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
