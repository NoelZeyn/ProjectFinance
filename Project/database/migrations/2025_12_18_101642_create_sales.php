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
        Schema::create('sales', function (Blueprint $table) {
            $table->id('id_sales');
            $table->unsignedBigInteger('id_admin_fk');
            $table->unsignedBigInteger('id_barang_sales');
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('customer_contact')->nullable();
            $table->string('payment_method');
            $table->string('payment_status')->nullable();
            $table->string('sales_status')->nullable();
            $table->string('shipping_status')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('courier')->nullable();
            $table->integer('shipping_cost')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('total_payment');
            $table->string('notes')->nullable();
            $table->date('date');
            // $table->string('product_name');
            // $table->string('product_code');
            // $table->string('category');
            $table->integer('quantity');
            // $table->integer('price');
            $table->integer('total');
            $table->timestamps();
            $table->foreign('id_barang_sales')->references('id_barang_sales')->on('barang_sales');
            $table->foreign('id_admin_fk')->references('id')->on('admin');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
