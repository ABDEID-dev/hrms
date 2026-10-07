<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fingerprint extends Model
{
    use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'log',
        'check_in',
        'check_out',
        'is_checked',
        'excuse',
        'system_open_latitude',
        'system_open_longitude',
        'system_open_accuracy',
        'system_open_at',
        'system_open_photo_path',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_accuracy',
        'check_in_photo_path',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_accuracy',
        'check_out_photo_path',
    ];

    // 👉 Links
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // 👉 Attributes
    protected function checkIn(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => $value !== null ? Carbon::parse($value)->format('H:i') : '');
    }

    protected function checkOut(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => $value !== null ? Carbon::parse($value)->format('H:i') : '');
    }

    // 👉 Scopes
    public function scopeFilteredFingerprints(
        Builder $query,
        $selectedEmployeeId,
        $fromDate,
        $toDate,
        $isAbsence,
        $isOneFingerprint,
        $weeklyHoliday = null
    ): void {
        $query
            ->where('employee_id', $selectedEmployeeId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->when($isAbsence, function ($query) {
                return $query->whereNull('log');
            })
            ->when($isOneFingerprint, function ($query) {
                return $query->whereNotNull('check_in')->whereNull('check_out');
            })
            ->when($weeklyHoliday !== null, function ($query) use ($weeklyHoliday) {
                return $query->whereRaw('DAYOFWEEK(`date`) != ?', [$weeklyHoliday + 1]);
            })
            ->orderBy('date');
    }

    // 👉 Check if employee has weekly holiday on this date
    public static function isWeeklyHoliday(int $employeeId, $date): bool
    {
        $employee = Employee::find($employeeId);
        if (! $employee || $employee->weekly_holiday === null) {
            return false;
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0=Sunday, 6=Saturday
        return $dayOfWeek === $employee->weekly_holiday;
    }
}
