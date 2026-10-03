<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    protected $fillable = ['number', 'name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getLabelAttribute(): string
    {
        return __('Shelf').' '.$this->number.' · '.$this->name;
    }
}
