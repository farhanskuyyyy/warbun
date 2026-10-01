<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(RefundItem::class);
    }

    public function refundable()
    {
        return $this->morphTo();
    }
}
