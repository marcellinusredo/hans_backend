<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JasaController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\PengadaanStokController;

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::apiResource('role', RoleController::class);
    Route::apiResource('staff', StaffController::class);
    Route::get('supplier/dropdown', [SupplierController::class, 'getDropdown']);
    Route::apiResource('supplier', SupplierController::class);
    Route::get('pelanggan/dropdown', [PelangganController::class, 'getDropdown']);
    Route::apiResource('pelanggan', PelangganController::class);
    Route::apiResource('kategori', KategoriController::class);
    Route::get('produk/dropdown', [ProdukController::class, 'getDropdown']);
    Route::get('produk/dropdown-transaksi', [ProdukController::class, 'getDropdownTransaksi']);
    Route::apiResource('produk', ProdukController::class);
    Route::get('jasa/dropdown', [JasaController::class, 'getDropdown']);
    Route::apiResource('jasa', JasaController::class);
    Route::get('pengadaan-stok/{id}/invoice', [PengadaanStokController::class, 'cetakInvoice']);
    Route::get('pengadaan-stok/{id}/detail', [PengadaanStokController::class, 'getDetail']);
    Route::apiResource('pengadaan-stok', PengadaanStokController::class);
    Route::get('transaksi/{id}/detail', [TransaksiController::class, 'showDetailTransaksi']);
    Route::get('transaksi/{id}/invoice', [TransaksiController::class, 'cetakInvoice']);;
    Route::apiResource('transaksi', TransaksiController::class);
    Route::prefix('laporan')->group(function () {
        Route::get('/transaksi', [LaporanController::class, 'transaksi']);
        Route::get('/produk-terlaris', [LaporanController::class, 'produkTerlaris']);
        Route::get('/jasa-terlaris', [LaporanController::class, 'jasaTerlaris']);
        Route::get('/pengadaan-produk', [LaporanController::class, 'pengadaanProduk']);
        Route::get('/stok-produk', [LaporanController::class, 'stokProduk']);
        Route::get('/keuangan', [LaporanController::class, 'keuangan']);
        Route::get('/opsi', [LaporanController::class, 'opsiLaporan']);
        // Export routes
        Route::get('/{jenis}/export/excel', [LaporanController::class, 'exportExcel']);
        Route::get('/{jenis}/export/pdf', [LaporanController::class, 'exportPdf']);
    });
});
