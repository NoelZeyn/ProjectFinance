<?php

namespace Database\Seeders;

use App\Models\Sales;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
            DB::table('sales')->truncate();
        // Add your sales data seeding logic here
        for ($i = 1; $i <= 10000; $i++) {
            Sales::create([
                'id_admin_fk' => 1,
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'customer_name' => 'Customer ' . $i,
                'customer_contact' => 'customer' . $i . '@example.com',
                'payment_method' => 'Credit Card',
                'payment_status' => 'Paid',
                'sales_status' => 'Completed',
                'shipping_status' => 'Delivered',
                'tracking_number' => 'TRACK' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'shipping_address' => '123 Main St, City ' . $i,
                'city' => 'City ' . $i,
                'province' => 'Province ' . $i,
                'postal_code' => '1000' . $i,
                'country' => 'Country ' . $i,
                'courier' => 'DHL',
                'shipping_cost' => rand(5000, 20000),
                'discount' => rand(0, 5000),
                'total_payment' => rand(50000, 200000),
                'notes' => 'This is a note for sale #' . $i,
                'date' => now()->subDays(rand(0, 365)),
                // 'product_name' => 'Product ' . $i,
                // 'product_code' => 'PROD' . str_pad($i, 5, '0', STR_PAD_LEFT),
                // 'category' => 'Category ' . rand(1, 5),
                'quantity' => rand(1, 10),
                // 'price' => rand(10000, 50000),
                'id_barang_sales' => rand(1, 10),
                'total' => rand(10000, 500000),
            ]);
        }
    }
}
