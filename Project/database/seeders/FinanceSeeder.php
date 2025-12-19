<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['income', 'expense'];

        for ($i = 1; $i <= 22; $i++) {

            $amount = rand(1, 20);
            $price  = rand(1000, 10000);
            $total  = $amount * $price;

            DB::table('finance')->insert([
                'id_admin_fk' => 1,
                'date'        => now()->subDays(rand(0, 365)),
                'type'        => $types[array_rand($types)], // ENUM
                'item'        => 'Item ' . $i,
                'category'    => 'Category ' . rand(1, 5),
                'description' => 'Description for finance entry ' . $i,
                'amount'      => $amount,
                'price'       => $price,
                'total'       => $total,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
