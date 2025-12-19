<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance', function (Blueprint $table) {
            $table->id('id_finance');
            $table->unsignedBigInteger('id_admin_fk');
            $table->enum('type', ['income', 'expense']);
            $table->date('date');

            // Detail Item
            $table->string('item');
            $table->string('category');
            $table->string('description')->nullable();

            // Perhitungan Uang
            $table->bigInteger('amount'); // Qty (Jumlah Barang)
            $table->bigInteger('price');  // Harga Satuan
            $table->bigInteger('total');  // Total (Qty * Price) -> Uang yang keluar/masuk

            // PENTING: Saldo saat transaksi ini terjadi
            $table->bigInteger('current_balance')->default(0);

            $table->timestamps();
            $table->foreign('id_admin_fk')->references('id')->on('admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance');
    }
};
