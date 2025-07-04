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
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail_transaksi');
            $table->foreignId('produk_id')->constrained('produk','id_produk')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->foreignId('transaksi_id')->constrained('transaksi','id_transaksi')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->integer('jumlah_produk_detail_transaksi')->unsigned()->default(0);
            $table->decimal('harga_produk_detail_transaksi',20,2)->unsigned()->default(0);
            $table->decimal('subtotal_produk_detail_transaksi',20,2)->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
