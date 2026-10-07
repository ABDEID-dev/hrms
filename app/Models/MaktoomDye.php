<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaktoomDye extends Model
{
    use CreatedUpdatedDeletedBy, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'warehouse_stock',
        'shop_stock',
        'sold_quantity',
        'low_stock_threshold',
        'is_active',
        'note',
    ];

    protected $casts = [
        'warehouse_stock' => 'integer',
        'shop_stock' => 'integer',
        'sold_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(MaktoomDyeMovement::class);
    }
}
