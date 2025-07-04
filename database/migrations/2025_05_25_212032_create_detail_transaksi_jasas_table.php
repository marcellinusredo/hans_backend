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
        Schema::create('detail_transaksi_jasa', function (Blueprint $table) {
            $table->id('id_detail_transaksi_jasa');
            $table->foreignId('transaksi_id')->constrained('transaksi','id_transaksi')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->foreignId('jasa_id')->constrained('jasa','id_jasa')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->decimal('harga_jasa_detail_transaksi_jasa',20,2)->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_jasa');
    }
};
