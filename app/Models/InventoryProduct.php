<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryProduct extends Model
{
    use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'account',
        'category',
        'name',
        'sku',
        'color',
        'length_cm',
        'unit',
        'stock_quantity',
        'sold_quantity',
        'unit_price',
        'low_stock_threshold',
        'image_path',
        'is_active',
        'note',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:3',
        'sold_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'low_stock_threshold' => 'decimal:3',
        'is_active' => 'boolean',
        'length_cm' => 'integer',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function unitLabel(): string
    {
        return $this->unit === 'gram' ? 'gram' : 'piece';
    }
}
