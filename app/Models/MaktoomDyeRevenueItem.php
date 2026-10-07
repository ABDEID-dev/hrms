<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaktoomDyeRevenueItem extends Model
{
    use CreatedUpdatedDeletedBy, SoftDeletes;

    protected $fillable = [
        'maktoom_dye_revenue_id',
        'maktoom_dye_id',
        'quantity',
        'stock_before',
        'stock_after',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public function revenue(): BelongsTo
    {
        return $this->belongsTo(MaktoomDyeRevenue::class, 'maktoom_dye_revenue_id');
    }

    public function dye(): BelongsTo
    {
        return $this->belongsTo(MaktoomDye::class, 'maktoom_dye_id');
    }
}
