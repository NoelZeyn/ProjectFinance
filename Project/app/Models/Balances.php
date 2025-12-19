<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balances extends Model
{
    use HasFactory;
    protected $table = 'balances';
    protected $primaryKey = 'id_balance';
    protected $fillable = [
        'id_admin_fk',
        'month',
        'year',
        'initial_balance',
        'total_income',
        'total_expense',
        'ending_balance',
        'is_finalized',
    ];
    protected $casts = [
        'initial_balance' => 'integer',
        'total_income'    => 'integer',
        'total_expense'   => 'integer',
        'ending_balance'  => 'integer',
        'is_finalized'    => 'boolean',
    ];

    // Relasi ke Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin_fk', 'id');
    }
}
