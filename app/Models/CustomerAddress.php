<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = ['label', 'recipient_name', 'phone', 'address', 'latitude', 'longitude', 'is_default'];

    protected $casts = ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'is_default' => 'boolean'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shippingText(): string
    {
        return $this->recipient_name.($this->phone ? ' · '.$this->phone : '')."\n".$this->address;
    }
}
