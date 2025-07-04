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
        Schema::create('detail_pengadaan_stok', function (Blueprint $table) {
            $table->id('id_detail_pengadaan_stok');
            $table->foreignId('produk_id')->constrained('produk','id_produk')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->foreignId('pengadaan_stok_id')->constrained('pengadaan_stok','id_pengadaan_stok')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->decimal('harga_produk_detail_pengadaan_stok',20,2)->unsigned()->default(0);
            $table->integer('jumlah_produk_detail_pengadaan_stok')->unsigned()->default(0);
            $table->decimal('subtotal_produk_detail_pengadaan_stok',20,2)->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pengadaan_stok');
    }
};
