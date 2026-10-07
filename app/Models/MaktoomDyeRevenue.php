<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaktoomDyeRevenue extends Model
{
    use CreatedUpdatedDeletedBy, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'total_quantity',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'total_quantity' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaktoomDyeRevenueItem::class);
    }
}
