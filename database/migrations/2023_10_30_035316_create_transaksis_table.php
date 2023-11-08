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
            $table->id();
            $table->unsignedInteger('pesanan_id');
            $table->string('tipe_pembayaran')->default('none');
            $table->string('status_pembayaran')->default('none');
            $table->float('diskon')->default(0);
            $table->float('subtotal_produk')->default(0);
            $table->float('subtotal_pengiriman')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
