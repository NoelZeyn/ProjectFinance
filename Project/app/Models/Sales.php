<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sales extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales';
    protected $primaryKey = 'id_sales';

    protected $fillable = [
        'id_admin_fk',
        'invoice_number',
        'customer_name',
        'customer_contact',
        'payment_method',
        'payment_status',
        'sales_status',
        'shipping_status',
        'tracking_number',
        'shipping_address',
        'city',
        'province',
        'postal_code',
        'country',
        'courier',
        'shipping_cost',
        'discount',
        'total_payment',
        'notes',
        'date',
        // 'product_name',
        // 'product_code',
        // 'category',
        'quantity',
        // 'price',
        'total',    
        'id_barang_sales'
    ];

    // Relasi ke Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin_fk', 'id');
    }
    public function barangSales()
    {
        return $this->belongsTo(BarangSales::class, 'id_barang_sales', 'id_barang_sales');
    }
}
