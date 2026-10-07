<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'account',
        'type',
        'revenue_kind',
        'employee_id',
        'payroll_month',
        'inventory_product_id',
        'expense_kind',
        'date',
        'employee_name',
        'service',
        'quantity',
        'unit_price',
        'amount',
        'payment_method',
        'has_invoice',
        'invoice_image_path',
        'withdrawn_to',
        'note',
        'customer_name',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'has_invoice' => 'boolean',
    ];

    public function inventoryProduct(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected function date(): Attribute
    {
        return Attribute::make(get: fn (string $value) => Carbon::parse($value)->format('Y-m-d'));
    }
}
