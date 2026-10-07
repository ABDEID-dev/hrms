<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeRequest extends Model
{
    use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type',
        'complainant_name',
        'complaint_against_employee_id',
        'complaint_against_other',
        'title',
        'body',
        'admin_response',
        'amount',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
        'voice_path',
        'voice_mime',
        'status',
        'reviewed_by',
        'reviewed_at',
        'employee_hidden_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'employee_hidden_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function complaintAgainstEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'complaint_against_employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeRequestDocument::class);
    }

    public function visibleDocuments(): HasMany
    {
        return $this->documents()
            ->whereNull('removed_from_complaint_at')
            ->whereNull('deleted_from_system_at');
    }
}
