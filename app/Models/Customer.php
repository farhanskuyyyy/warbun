<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'phone', 'email', 'address', 'latitude', 'longitude',
        'is_active', 'can_use_debt', 'credit_limit',
        'outstanding_balance', 'debt_status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'can_use_debt' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function debtAccount()
    {
        return $this->hasOne(DebtAccount::class);
    }

    public function debtTransactions()
    {
        return $this->hasMany(DebtTransaction::class);
    }

    public function hasDebt()
    {
        return $this->outstanding_balance > 0;
    }

    public function canUseCredit($amount)
    {
        if (! $this->can_use_debt) {
            return false;
        }

        return ($this->outstanding_balance + $amount) <= $this->credit_limit;
    }
}
