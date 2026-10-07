<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'english_name',
        'phone',
        'nationality',
        'nationality_en',
        'note',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(CustomerService::class)->latest('served_at')->latest('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalonInvoice::class)->latest('invoice_date')->latest('id');
    }
}
