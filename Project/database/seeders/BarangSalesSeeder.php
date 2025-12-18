<?php

namespace Database\Seeders;

use App\Models\BarangSales;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarangSalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            BarangSales::create([
                'product_name' => 'Product ' . $i,
                'product_code' => 'P' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'category' => 'Category ' . (($i % 3) + 1),
                'quantity' => rand(1, 100),
                'price' => rand(10000, 500000),
            ]);
        }
    }
}
