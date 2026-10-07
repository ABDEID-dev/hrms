<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalonInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_invoice_id',
        'salon_service_id',
        'service_name',
        'service_name_en',
        'unit_price',
        'quantity',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalonInvoice::class, 'salon_invoice_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SalonService::class, 'salon_service_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'salon_invoice_item_employee')
            ->withTimestamps()
            ->orderBy('employees.first_name')
            ->orderBy('employees.last_name');
    }
}
