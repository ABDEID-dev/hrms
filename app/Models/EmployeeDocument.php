<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory;

    public const TYPE_IDENTITY = 'identity';
    public const TYPE_ADVANCE_RECEIPT = 'advance_receipt';
    public const TYPE_SALARY_RECEIPT = 'salary_receipt';

    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_IDENTITY => 'هوية الموظف',
            self::TYPE_ADVANCE_RECEIPT => 'إيصال السلفة',
            self::TYPE_SALARY_RECEIPT => 'إيصال تحصيل الراتب',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? $this->type;
    }

    public function getUrlAttribute(): string
    {
        return route('employee-documents-file', $this, false);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->mime === 'application/pdf' || str_ends_with(strtolower($this->path), '.pdf');
    }

    public function getReadableSizeAttribute(): string
    {
        if (! $this->size) {
            return '---';
        }

        return $this->size >= 1048576
            ? number_format($this->size / 1048576, 2).' MB'
            : number_format($this->size / 1024, 1).' KB';
    }
}
