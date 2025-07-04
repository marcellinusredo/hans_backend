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
        Schema::create('pengadaan_stok', function (Blueprint $table) {
            $table->id('id_pengadaan_stok');
            $table->foreignId('supplier_id')->constrained('supplier','id_supplier')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->foreignId('staff_id')->constrained('staff','id_staff')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->string('nomor_invoice_pengadaan_stok')->unique();
            $table->decimal('total_harga_pengadaan_stok',20,2)->unsigned()->default(0);
            $table->date('waktu_pengadaan_stok');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengadaan_stok');
    }
};
