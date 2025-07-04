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
        Schema::create('staff', function (Blueprint $table) {
            $table->id('id_staff');
            $table->foreignId('role_id')->constrained('role','id_role')
            ->onUpdate('cascade')
            ->onDelete('restrict');
            $table->string('nama_staff');
            $table->string('nomor_telp_staff')->nullable();
            $table->text('alamat_staff')->nullable();
            $table->string('username_staff')->unique();
            $table->string('password_staff');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
