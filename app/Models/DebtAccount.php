<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtAccount extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'total_debt', 'total_paid', 'outstanding_balance', 'credit_limit', 'status'];

    protected $casts = [
        'total_debt' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions()
    {
        return $this->hasMany(DebtTransaction::class);
    }

    public function hasAvailableCredit($amount)
    {
        return ($this->outstanding_balance + $amount) <= $this->credit_limit;
    }
}
