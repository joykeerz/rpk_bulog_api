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
            $table->id();
            $table->unsignedInteger('kategori_id');
            $table->string('kode_produk');
            $table->string('nama_produk');
            $table->string('desk_produk');
            $table->float('harga_produk')->default(0);
            $table->float('diskon_produk')->default(0);
            $table->string('satuan_unit_produk');
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
