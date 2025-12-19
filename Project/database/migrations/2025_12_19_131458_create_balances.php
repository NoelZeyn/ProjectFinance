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
        Schema::create('balances', function (Blueprint $table) {
            $table->id('id_balance');
            $table->unsignedBigInteger('id_admin_fk');
            $table->integer('month');
            $table->integer('year');
            $table->integer('initial_balance');
            $table->integer('total_income')->default(0);
            $table->integer('total_expense')->default(0);
            $table->integer('ending_balance');
            $table->boolean('is_finalized')->default(false);
            $table->timestamps();
            $table->unique(['id_admin_fk', 'month', 'year'], 'unique_balance_per_admin_month_year');
            $table->foreign('id_admin_fk')->references('id')->on('admin');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
