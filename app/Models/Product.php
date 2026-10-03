<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'barcode', 'category_id', 'product_type_id',
        'brand_id', 'unit_id', 'supplier_id', 'description',
        'cost_price', 'selling_price', 'minimum_stock', 'current_stock',
        'is_active', 'is_available_online', 'is_featured', 'image', 'weight', 'shelf_id', 'shelf_position',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'is_available_online' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function getLocationLabelAttribute(): string
    {
        return $this->shelf ? $this->shelf->label.($this->shelf_position ? ' / '.$this->shelf_position : '') : __('Location not assigned');
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class)->withTrashed();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class)->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }
}
