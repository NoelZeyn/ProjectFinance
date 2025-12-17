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
        Schema::create('finance', function (Blueprint $table) {
            $table->id('id_finance');
            $table->unsignedBigInteger('id_admin_fk');
            $table->date('date');
            $table->string('item');
            $table->string('category');
            $table->string('description');
            $table->integer('amount');
            $table->integer('price');
            $table->integer('total');
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
