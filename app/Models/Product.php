<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'image',
        'category',
        'description',
        'rate',
        'is_active',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rate' => 'decimal:1',
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    /**
     * Get the order items for this product
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
