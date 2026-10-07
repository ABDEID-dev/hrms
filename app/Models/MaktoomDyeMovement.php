<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaktoomDyeMovement extends Model
{
    use CreatedUpdatedDeletedBy, SoftDeletes;

    protected $fillable = [
        'maktoom_dye_id',
        'type',
        'quantity',
        'warehouse_balance_after',
        'shop_balance_after',
        'sold_quantity_after',
        'occurred_at',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'warehouse_balance_after' => 'integer',
        'shop_balance_after' => 'integer',
        'sold_quantity_after' => 'integer',
        'occurred_at' => 'datetime',
    ];

    public function dye(): BelongsTo
    {
        return $this->belongsTo(MaktoomDye::class, 'maktoom_dye_id');
    }
}
