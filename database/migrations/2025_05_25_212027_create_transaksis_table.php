<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->foreignId('pelanggan_id')->constrained('pelanggan','id_pelanggan')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->foreignId('staff_id')->constrained('staff','id_staff')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->string('nomor_invoice_transaksi')->unique(); 
            $table->date('waktu_transaksi');
            $table->decimal('total_harga_transaksi',20,2)->unsigned()->default(0);
            $table->decimal('pembayaran_transaksi',20,2)->unsigned()->default(0);
            $table->decimal('kembalian_transaksi',20,2)->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
