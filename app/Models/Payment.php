<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number', 'payable_type', 'payable_id', 'customer_id',
        'amount', 'method', 'status', 'reference_number', 'gateway_reference',
        'notes', 'user_id', 'paid_at', 'expires_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generatePaymentNumber()
    {
        return 'PAY-' . date('Ymd') . '-' . str_pad(self::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT);
    }
}
