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
        Schema::create('barang_sales', function (Blueprint $table) {
            $table->id('id_barang_sales');
            $table->string('product_name');
            $table->string('product_code');
            $table->string('category');
            $table->integer('quantity');
            $table->integer('price');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_sales');
    }
};
