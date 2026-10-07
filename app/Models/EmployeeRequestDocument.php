<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRequestDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_request_id',
        'employee_id',
        'kind',
        'path',
        'original_name',
        'mime',
        'size',
        'removed_from_complaint_at',
        'removed_from_complaint_by',
        'deleted_from_system_at',
        'deleted_from_system_by',
    ];

    protected $casts = [
        'removed_from_complaint_at' => 'datetime',
        'deleted_from_system_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(EmployeeRequest::class, 'employee_request_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getUrlAttribute(): string
    {
        return route('employee-complaints-file', ['path' => ltrim((string) $this->path, '/')]);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return str_starts_with((string) $this->mime, 'video/');
    }

    public function getIsAudioAttribute(): bool
    {
        return str_starts_with((string) $this->mime, 'audio/') || $this->kind === 'voice';
    }
}
