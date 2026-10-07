<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalonInvoice extends Model
{
    use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'salon_service_id',
        'employee_id',
        'branch',
        'invoice_language',
        'service_price',
        'paid_amount',
        'payment_method',
        'has_warranty',
        'warranty_note',
        'note',
        'invoice_date',
        'is_whatsapp_sent',
        'whatsapp_sent_at',
        'whatsapp_error',
    ];

    protected $casts = [
        'service_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'has_warranty' => 'boolean',
        'invoice_date' => 'datetime',
        'is_whatsapp_sent' => 'boolean',
        'whatsapp_sent_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SalonService::class, 'salon_service_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalonInvoiceItem::class)->orderBy('id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'salon_invoice_employee')
            ->withTimestamps()
            ->orderBy('employees.first_name')
            ->orderBy('employees.last_name');
    }
}
