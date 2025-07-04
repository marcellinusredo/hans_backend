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
        Schema::create('produk', function (Blueprint $table) {
            $table->id('id_produk');
            $table->foreignId('kategori_id')->constrained('kategori','id_kategori')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->string('nama_produk');
            $table->string('kode_produk')->unique();
            $table->decimal('harga_produk',20,2)->unsigned()->default(0);
            $table->integer('stok_produk')->unsigned()->default(0);
            $table->text('deskripsi_produk')->nullable();
            $table->string('gambar_produk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
