<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLocationEvent extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'event_type',
        'latitude',
        'longitude',
        'accuracy',
        'occurred_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
