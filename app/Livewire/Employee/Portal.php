<?php

namespace App\Livewire\Employee;

use App\Models\Discount;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\EmployeeRequest;
use App\Models\Fingerprint;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\DefaultNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Portal extends Component
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    public ?Employee $employee = null;

    public $todayFingerprint;

    public $latestDiscounts = [];

    public $salarySummary = [];

    public $discountSummary = [];

    public $attendanceSummary = [
        'work_days' => 0,
        'worked_duration' => '00:00',
    ];

    public string $requestType = 'advance';

    public $advanceInfo = [
        'amount' => null,
        'note' => '',
    ];

    public $adminMessage = [
        'title' => '',
        'body' => '',
    ];

    public $leaveNotice = [
        'active' => null,
        'upcoming' => null,
    ];

    public function mount()
    {
        $this->employee = Auth::user()->employee;

        abort_if(! $this->employee, 404, 'Employee profile not found.');

        $this->loadLeaveNotice();
        $this->loadPortalData();
    }

    public function render()
    {
        $this->loadPortalData();

        return view('livewire.employee.portal');
    }

    public function canUseSelfFingerprint(): bool
    {
        if (($this->leaveNotice['active'] ?? null) === null) {
            $this->loadLeaveNotice();
        }

        return Auth::check()
            && Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee'])
            && ! Auth::user()->hasRole('Admin')
            && empty($this->leaveNotice['active']);
    }

    public function clockIn(?array $location = null)
    {
        abort_if(! $this->canUseSelfFingerprint(), 403, 'Unauthorized action.');

        $employeeId = Auth::user()->employee_id;
        $now = Carbon::now(self::OFFICIAL_TIMEZONE);

        $fingerprint = Fingerprint::firstOrNew([
            'employee_id' => $employeeId,
            'date' => $now->toDateString(),
        ]);

        if ($fingerprint->check_in) {
            $this->dispatch('toastr', type: 'info', message: __('Your attendance has already been recorded for today.'));

            return;
        }

        if (! $this->hasValidFingerprintLocation($location)) {
            $this->dispatch('toastr', type: 'error', message: 'يجب السماح بتحديد الموقع قبل تسجيل بصمة الدخول.');

            return;
        }

        $fingerprint->fill([
            'log' => $now->format('H:i'),
            'check_in' => $now->format('H:i:s'),
        ] + $this->locationData($location, 'check_in'))->save();

        $this->loadPortalData();
        $this->dispatch('toastr', type: 'success', message: __('Attendance recorded successfully.'));
    }

    public function clockOut(?array $location = null)
    {
        abort_if(! $this->canUseSelfFingerprint(), 403, 'Unauthorized action.');

        $now = Carbon::now(self::OFFICIAL_TIMEZONE);
        $fingerprint = Fingerprint::where('employee_id', Auth::user()->employee_id)
            ->where('date', $now->toDateString())
            ->first();

        if (! $fingerprint || ! $fingerprint->check_in) {
            $this->dispatch('toastr', type: 'error', message: __('You need to record attendance first.'));

            return;
        }

        if ($fingerprint->check_out) {
            $this->dispatch('toastr', type: 'info', message: __('Departure has already been recorded for today.'));

            return;
        }

        if (! $this->hasValidFingerprintLocation($location)) {
            $this->dispatch('toastr', type: 'error', message: 'يجب السماح بتحديد الموقع قبل تسجيل بصمة الخروج.');

            return;
        }

        $fingerprint->update([
            'log' => Carbon::parse($fingerprint->check_in)->format('H:i').' '.$now->format('H:i'),
            'check_out' => $now->format('H:i:s'),
        ] + $this->locationData($location, 'check_out'));

        $this->loadPortalData();
        $this->dispatch('toastr', type: 'success', message: __('Departure recorded successfully.'));
    }

    public function getLeaveName(?int $leaveId): string
    {
        return Leave::find($leaveId)?->name ?? '---';
    }

    private function locationData(?array $location, string $prefix): array
    {
        if (! $this->hasFingerprintLocationColumns()) {
            return [];
        }

        return [
            "{$prefix}_latitude" => round((float) $location['latitude'], 7),
            "{$prefix}_longitude" => round((float) $location['longitude'], 7),
            "{$prefix}_accuracy" => isset($location['accuracy']) ? round((float) $location['accuracy'], 2) : null,
        ];
    }

    private function hasValidFingerprintLocation(?array $location): bool
    {
        if (! $this->hasFingerprintLocationColumns()) {
            return false;
        }

        $latitude = $location['latitude'] ?? null;
        $longitude = $location['longitude'] ?? null;

        return is_numeric($latitude)
            && is_numeric($longitude)
            && (float) $latitude >= -90
            && (float) $latitude <= 90
            && (float) $longitude >= -180
            && (float) $longitude <= 180;
    }

    private function hasFingerprintLocationColumns(): bool
    {
        return Schema::hasColumn('fingerprints', 'check_in_latitude')
            && Schema::hasColumn('fingerprints', 'check_out_latitude');
    }

    public function getLeaveDurationLabel(EmployeeLeave $leave): string
    {
        if ($leave->start_at && $leave->end_at) {
            $startAt = Carbon::parse(trim($leave->from_date.' '.$leave->start_at));
            $endAt = Carbon::parse(trim($leave->to_date.' '.$leave->end_at));
            $totalMinutes = max(0, $startAt->diffInMinutes($endAt));

            return $this->humanizeLeaveMinutes($totalMinutes);
        }

        $days = max(1, Carbon::parse($leave->from_date)->diffInDays(Carbon::parse($leave->to_date)) + 1);

        return $this->humanizeLeaveMinutes($days * 24 * 60);
    }

    private function humanizeLeaveMinutes(int $totalMinutes): string
    {
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $isArabic = str_starts_with((string) app()->getLocale(), 'ar');

        if ($hours > 0 && $minutes > 0) {
            return $isArabic
                ? "{$hours} ساعة و {$minutes} دقيقة"
                : "{$hours} hours and {$minutes} minutes";
        }

        if ($hours > 0) {
            return $isArabic ? "{$hours} ساعة" : ($hours === 1 ? '1 hour' : "{$hours} hours");
        }

        return $isArabic ? "{$minutes} دقيقة" : ($minutes === 1 ? '1 minute' : "{$minutes} minutes");
    }

    public function submitAdvanceRequest(): void
    {
        $this->validate([
            'advanceInfo.amount' => 'required|numeric|min:1',
            'advanceInfo.note' => 'required|string|min:5|max:2000',
        ]);

        $request = EmployeeRequest::create([
            'employee_id' => Auth::user()->employee_id,
            'type' => 'advance',
            'title' => __('ui.advance_request'),
            'body' => $this->advanceInfo['note'],
            'amount' => $this->advanceInfo['amount'],
            'status' => 'pending',
        ]);

        $this->notifyManagement(
            __('ui.advance_request_from_employee', ['name' => $this->employee->full_name]).' - '.__('ui.amount').': '.$request->amount,
            route('messages-employee-requests', ['request' => $request->id], false)
        );

        $this->reset('advanceInfo');
        $this->loadPortalData();
        $this->dispatch('toastr', type: 'success', message: __('ui.advance_request_sent'));
    }

    public function sendMessageToAdministration(): void
    {
        $this->validate([
            'adminMessage.title' => 'required|string|min:3|max:255',
            'adminMessage.body' => 'required|string|min:5|max:3000',
        ]);

        $request = EmployeeRequest::create([
            'employee_id' => Auth::user()->employee_id,
            'type' => 'message',
            'title' => $this->adminMessage['title'],
            'body' => $this->adminMessage['body'],
            'status' => 'pending',
        ]);

        $this->notifyManagement(
            __('ui.employee_message_from', ['name' => $this->employee->full_name]).' - '.$this->adminMessage['title'],
            route('messages-employee-requests', ['request' => $request->id], false)
        );

        $this->reset('adminMessage');
        $this->loadPortalData();
        $this->dispatch('toastr', type: 'success', message: __('ui.message_sent_to_administration'));
    }

    private function loadPortalData(): void
    {
        $user = Auth::user();
        $baseSalary = (float) ($this->employee->basic_salary ?? 0);
        $housingAllowance = (float) ($this->employee->housing_allowance ?? 0);
        $transportationAllowance = (float) ($this->employee->transportation_allowance ?? 0);
        $salaryTotal = $baseSalary + $housingAllowance + $transportationAllowance;
        $monthStart = Carbon::now(self::OFFICIAL_TIMEZONE)->startOfMonth()->toDateString();
        $monthEnd = Carbon::now(self::OFFICIAL_TIMEZONE)->endOfMonth()->toDateString();
        $monthlyFingerprints = Fingerprint::where('employee_id', $user->employee_id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->whereNotNull('check_in')
            ->get();

        $latestDiscounts = Discount::where('employee_id', $user->employee_id)
            ->latest('date')
            ->latest('id')
            ->take(8)
            ->get();

        $monthlyDiscounts = Discount::where('employee_id', $user->employee_id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $this->loadLeaveNotice();
        $this->todayFingerprint = Fingerprint::where('employee_id', $user->employee_id)
            ->where('date', Carbon::today(self::OFFICIAL_TIMEZONE)->toDateString())
            ->first();

        $this->latestDiscounts = $latestDiscounts;

        $this->salarySummary = [
            'basic' => $baseSalary,
            'housing' => $housingAllowance,
            'transportation' => $transportationAllowance,
            'total' => $salaryTotal,
            'net' => max(0, $salaryTotal - (float) $monthlyDiscounts->where('rate', '>', 0)->sum('rate')),
        ];

        $this->discountSummary = [
            'count' => $monthlyDiscounts->count(),
            'cash_total' => (float) $monthlyDiscounts->where('rate', '>', 0)->sum('rate'),
            'absence_count' => $monthlyDiscounts->filter(fn ($discount) => str_contains((string) $discount->reason, 'غياب') || str_contains((string) $discount->reason, 'Absent'))->count(),
            'latest_batch' => null,
        ];

        $this->attendanceSummary = [
            'work_days' => $monthlyFingerprints->pluck('date')->unique()->count(),
            'worked_duration' => $this->formatWorkedSeconds($this->calculateWorkedSeconds($monthlyFingerprints)),
        ];

    }

    private function calculateWorkedSeconds($fingerprints): int
    {
        $now = Carbon::now(self::OFFICIAL_TIMEZONE);

        return $fingerprints->sum(function (Fingerprint $fingerprint) use ($now) {
            if (! $fingerprint->check_in) {
                return 0;
            }

            $checkIn = Carbon::parse($fingerprint->date.' '.$fingerprint->check_in, self::OFFICIAL_TIMEZONE);
            $checkOut = $fingerprint->check_out
                ? Carbon::parse($fingerprint->date.' '.$fingerprint->check_out, self::OFFICIAL_TIMEZONE)
                : ($fingerprint->date === $now->toDateString() ? $now : null);

            if (! $checkOut || $checkOut->lessThan($checkIn)) {
                return 0;
            }

            return $checkIn->diffInSeconds($checkOut);
        });
    }

    private function formatWorkedSeconds(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function loadLeaveNotice(): void
    {
        $this->leaveNotice = [
            'active' => null,
            'upcoming' => null,
        ];

        if (! $this->employee) {
            return;
        }

        $now = Carbon::now(self::OFFICIAL_TIMEZONE);
        $relevantLeaves = $this->employee->leaves()
            ->wherePivot('to_date', '>=', $now->toDateString())
            ->orderBy('employee_leave.from_date')
            ->orderBy('employee_leave.start_at')
            ->get();

        foreach ($relevantLeaves as $leave) {
            [$startAt, $endAt] = $this->resolveLeaveWindow($leave);

            if ($startAt <= $now && $endAt >= $now) {
                if (
                    ! $this->leaveNotice['active']
                    || $endAt->lt(Carbon::parse($this->leaveNotice['active']['end_at_iso']))
                ) {
                    $this->leaveNotice['active'] = $this->buildLeaveNoticeItem($leave, $startAt, $endAt, true);
                }

                continue;
            }

            if ($startAt->gt($now)) {
                if (
                    ! $this->leaveNotice['upcoming']
                    || $startAt->lt(Carbon::parse($this->leaveNotice['upcoming']['start_at_iso']))
                ) {
                    $this->leaveNotice['upcoming'] = $this->buildLeaveNoticeItem($leave, $startAt, $endAt, false);
                }
            }
        }
    }

    private function resolveLeaveWindow(Leave $leave): array
    {
        $startAt = Carbon::parse($leave->pivot->from_date);
        $endAt = Carbon::parse($leave->pivot->to_date);

        if ($this->leaveRequiresTime($leave->id)) {
            $startAt = Carbon::parse(trim($leave->pivot->from_date.' '.($leave->pivot->start_at ?: '00:00:00')));
            $endAt = Carbon::parse(trim($leave->pivot->to_date.' '.($leave->pivot->end_at ?: '23:59:59')));
        } else {
            $startAt = $startAt->startOfDay();
            $endAt = $endAt->endOfDay();
        }

        return [$startAt, $endAt];
    }

    private function buildLeaveNoticeItem(Leave $leave, Carbon $startAt, Carbon $endAt, bool $isActive): array
    {
        return [
            'leave_id' => $leave->id,
            'name' => $leave->name,
            'start_at_iso' => $startAt->toIso8601String(),
            'end_at_iso' => $endAt->toIso8601String(),
            'target_at_ms' => ($isActive ? $endAt : $startAt)->timestamp * 1000,
            'window_label' => $this->formatLeaveWindowLabel($leave, $startAt, $endAt),
            'mode_label' => $isActive ? __('Ends at') : __('Starts at'),
            'mode_badge' => $isActive ? __('Leave is active now') : __('Upcoming leave'),
        ];
    }

    private function formatLeaveWindowLabel(Leave $leave, Carbon $startAt, Carbon $endAt): string
    {
        if (! $this->leaveRequiresTime($leave->id)) {
            $fromDate = $startAt->translatedFormat('Y-m-d');
            $toDate = $endAt->translatedFormat('Y-m-d');

            return $fromDate === $toDate ? $fromDate : $fromDate.' - '.$toDate;
        }

        return $startAt->translatedFormat('Y-m-d h:i A').' - '.$endAt->translatedFormat('Y-m-d h:i A');
    }

    private function leaveRequiresTime($leaveId): bool
    {
        return substr((string) $leaveId, 1, 1) == '2';
    }

    private function notifyManagement(string $message, ?string $url = null): void
    {
        $availableRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'HR', 'ManagementEmployee'])
            ->pluck('name')
            ->all();

        if (empty($availableRoles)) {
            return;
        }

        User::query()
            ->where('id', '!=', Auth::id())
            ->whereHas('roles', function ($query) use ($availableRoles) {
                $query->whereIn('name', $availableRoles)->where('guard_name', 'web');
            })
            ->get()
            ->each(function (User $user) use ($message, $url) {
                $user->notify(new DefaultNotification(Auth::id(), $message, $url, 'ti ti-file-text'));
            });
    }

}
