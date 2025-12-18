<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangSales extends Model
{
    use SoftDeletes, HasFactory;
    protected $table = 'barang_sales';
    protected $primaryKey = 'id_barang_sales';

    protected $fillable = [
        'product_name',
        'product_code',
        'category',
        'quantity',
        'price',
    ];

}
