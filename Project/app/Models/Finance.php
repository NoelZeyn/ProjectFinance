<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    use HasFactory;

    protected $table = 'finance';
    protected $primaryKey = 'id_finance';

    protected $fillable = [
        'id_admin_fk',
        'type',
        'date',
        'item',
        'category',
        'description',
        'amount',
        'price',
        'total',
        'current_balance'
    ];
    protected $casts = [
        'date'            => 'date',    // Otomatis jadi Carbon (bisa $data->date->format('d-m-Y'))
        'amount'          => 'integer',
        'price'           => 'integer',
        'total'           => 'integer',
        'current_balance' => 'integer',
    ];

    // Relasi ke Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin_fk', 'id');
    }

}
