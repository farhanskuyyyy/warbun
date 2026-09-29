<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number', 'debt_account_id', 'customer_id', 'sale_id',
        'type', 'debit_amount', 'credit_amount', 'balance_after',
        'due_date', 'status', 'user_id', 'description', 'notes'
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function debtAccount()
    {
        return $this->belongsTo(DebtAccount::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'paid';
    }
}
