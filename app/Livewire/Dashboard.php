<?php

namespace App\Livewire;

use App\Jobs\sendPendingMessages;
use App\Models\AccountTransaction;
use App\Models\AdminAlert;
use App\Models\BulkMessage;
use App\Models\Center;
use App\Models\Changelog;
use App\Models\Discount;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\Fingerprint;
use App\Models\Leave;
use App\Models\Message;
use App\Models\Timeline;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Number;
use Livewire\Component;
use Throwable;

class Dashboard extends Component
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    private const ATTENDANCE_DAY_START_HOUR = 11;

    private const ATTENDANCE_DAY_END_HOUR = 6;

    public $employee;

    public $accountBalance = ['status' => 400, 'balance' => '---', 'is_active' => '---'];

    public $messagesStatus = ['sent' => 0, 'unsent' => 0];

    public array $adminStats = [
        'treasury_cash' => 0,
        'expenses' => 0,
        'change_operations' => 0,
        'messages' => 0,
        'monthly_salaries' => 0,
        'monthly_employee_withdrawals' => 0,
    ];

    public $changelogs;

    public $activeEmployees;

    public $center;

    public $selectedEmployeeId;

    public $leaveTypes;

    public $employeeLeaveId;

    public $employeeLeaveRecord;

    public $isEdit = false;

    public $confirmedId;

    public $leaveRecords = [];

    public $newLeaveInfo = [
        'LeaveId' => '',
        'fromDate' => null,
        'toDate' => null,
        'startAt' => null,
        'endAt' => null,
        'note' => null,
    ];

    public $fromDateLimit;

    public $employeePhoto = 'profile-photos/.default-photo.jpg';

    public $latestBatch;

    public $employeeDiscounts;

    public $batchDates;

    public $showStatictics = 1;

    public $todayFingerprint;

    public $todayAttendanceRecords;

    public ?int $editingAttendanceId = null;

    public ?int $confirmedAttendanceId = null;

    public array $attendanceForm = [
        'date' => null,
        'checkIn' => null,
        'checkOut' => null,
    ];

    public $leaveNotice = [
        'active' => null,
        'upcoming' => null,
    ];

    public $weeklyHolidayNotice = null;

    public $managementAlert = null;

    public function mount()
    {
        $user = Employee::find(Auth::user()->employee_id);

        $this->employee = $user;

        $currentTimeline = $user?->timelines()
            ->whereNull('end_date')
            ->first();

        $center = $currentTimeline
            ? Center::find($currentTimeline->center_id)
            : null;

        $this->activeEmployees = $this->loadLeaveEmployees($user, $center);

        $this->selectedEmployeeId = Auth::user()->employee_id;
        $this->employeePhoto = $user->profile_photo_path;

        $this->leaveTypes = Leave::all();

        try {
            $this->accountBalance = $this->CheckAccountBalance();
        } catch (Throwable $th) {
            //
        }

        $this->employeeDiscounts = $this->getEmployeeDiscounts();

        $this->fromDateLimit = Carbon::now(self::OFFICIAL_TIMEZONE)
            ->subDays(30)
            ->format('Y-m-d');
        $this->changelogs = Changelog::latest()->get();

        $this->applyContractRestrictions();
        $this->loadLeaveNotice();
        $this->loadWeeklyHolidayNotice();
        $this->loadManagementAlert();
        $this->loadTodayFingerprint();
        $this->loadTodayAttendanceRecords();
        $this->loadAdminStats();
    }

    private function loadLeaveEmployees(?Employee $user, ?Center $center)
    {
        if (! $user) {
            return collect();
        }

        if (Auth::user()->hasRole('Admin')) {
            return Employee::query()
                ->where('is_active', 1)
                ->orderBy('first_name')
                ->get()
                ->map(fn (Employee $employee) => (object) ['employee' => $employee]);
        }

        if (Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee', 'Viewer'])) {
            return Timeline::where('employee_id', $user->id)
                ->whereNull('end_date')
                ->with('employee')
                ->get();
        }

        return $center ? $center->activeEmployees() : collect();
    }

    private function applyContractRestrictions(): void
    {
        try {
            $employee = $this->employee;
            if (! $employee) {
                return;
            }

            $contract = $employee->contract;
            if (! $contract || (int) $contract->work_rate !== 100) {
                $this->showStatictics = 0;
            }
        } catch (\Throwable $e) {
            //
        }
    }

    public function render()
    {
        $sent = Message::where('is_sent', 1)->count();
        $unsent = Message::where('is_sent', 0)->count();

        $this->messagesStatus = [
            'sent' => Number::format($sent ?? 0),
            'unsent' => Number::format($unsent ?? 0),
        ];

        if (Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee', 'Viewer'])) {
            $this->leaveRecords = EmployeeLeave::where('employee_id', Auth::user()->employee_id)
                ->whereBetween('created_at', [
                    Carbon::now(self::OFFICIAL_TIMEZONE)
                        ->subDays(7)
                        ->startOfDay(),
                    Carbon::now(self::OFFICIAL_TIMEZONE)->endOfDay(),
                ])
                ->orderBy('created_at')
                ->get();
        } else {
            $this->leaveRecords = EmployeeLeave::where('created_by', Auth::user()->name)
                ->where('created_at', '>=', Carbon::now(self::OFFICIAL_TIMEZONE)->subDays(30)->startOfDay())
                ->orderByDesc('created_at')
                ->get();
        }

        $this->loadLeaveNotice();
        $this->loadWeeklyHolidayNotice();
        $this->loadManagementAlert();
        $this->loadTodayFingerprint();
        $this->loadTodayAttendanceRecords();
        $this->loadAdminStats();

        return view('livewire.dashboard');
    }

    private function loadAdminStats(): void
    {
        if (! Auth::check() || ! Auth::user()->hasRole('Admin')) {
            return;
        }

        $transactions = AccountTransaction::query()->get();
        $cashRevenues = (float) $transactions
            ->where('type', 'revenue')
            ->where('payment_method', 'cash')
            ->sum('amount');
        $expenses = (float) $transactions
            ->where('type', 'expense')
            ->sum('amount');

        $monthlyPayrollStats = $this->monthlyPayrollStats();

        $this->adminStats = [
            'treasury_cash' => $cashRevenues - $expenses,
            'expenses' => $expenses,
            'change_operations' => $this->countAccountTransactionChanges(),
            'messages' => Message::query()->count() + BulkMessage::query()->count(),
            'monthly_salaries' => $monthlyPayrollStats['salaries'],
            'monthly_employee_withdrawals' => $monthlyPayrollStats['withdrawals'],
        ];
    }

    private function monthlyPayrollStats(): array
    {
        $month = Carbon::now(self::OFFICIAL_TIMEZONE);
        $fromDate = $month->copy()->startOfMonth()->toDateString();
        $toDate = $month->copy()->endOfMonth()->toDateString();

        $employees = Employee::query()
            ->with('user.roles')
            ->where('is_active', true)
            ->get()
            ->reject(fn (Employee $employee) => $employee->user?->hasRole('Admin'));

        $employeeIds = $employees->pluck('id')->all();

        return [
            'salaries' => (float) $employees->sum(fn (Employee $employee) => $this->employeeGrossSalary($employee)),
            'withdrawals' => $employeeIds === []
                ? 0
                : (float) Discount::query()
                    ->whereIn('employee_id', $employeeIds)
                    ->whereBetween('date', [$fromDate, $toDate])
                    ->sum('rate'),
        ];
    }

    private function employeeGrossSalary(Employee $employee): float
    {
        return (float) ($employee->basic_salary ?? 0)
            + (float) ($employee->housing_allowance ?? 0)
            + (float) ($employee->transportation_allowance ?? 0);
    }

    private function countAccountTransactionChanges(): int
    {
        $count = 0;
        $files = glob(storage_path('logs/activity*.log')) ?: [];

        foreach ($files as $file) {
            if (! File::exists($file)) {
                continue;
            }

            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (
                    str_contains($line, 'App\\\\Models\\\\AccountTransaction')
                    && (
                        str_contains($line, '"action":"updated"')
                        || str_contains($line, '"action":"deleted"')
                    )
                ) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function canUseSelfFingerprint(): bool
    {
        if (($this->leaveNotice['active'] ?? null) === null) {
            $this->loadLeaveNotice();
        }

        $this->loadWeeklyHolidayNotice();

        return Auth::check()
            && Auth::user()->employee_id
            && Auth::user()->can('use attendance fingerprints')
            && $this->isAttendanceWindowOpen()
            && empty($this->weeklyHolidayNotice)
            && empty($this->leaveNotice['active']);
    }

    public function clockIn(?array $location = null)
    {
        if (! $this->canUseSelfFingerprint()) {
            abort(403, 'Unauthorized action.');
        }

        $employeeId = Auth::user()->employee_id;
        $now = Carbon::now(self::OFFICIAL_TIMEZONE);
        $attendanceDate = $this->getCurrentAttendanceDate();

        $fingerprint = Fingerprint::firstOrNew([
            'employee_id' => $employeeId,
            'date' => $attendanceDate,
        ]);

        if ($fingerprint->check_in) {
            $this->dispatch('toastr', type: 'info', message: __('Your attendance has already been recorded for today.'));

            return;
        }

        if ($this->requiresAttendanceLocation() && ! $this->hasValidFingerprintLocation($location)) {
            $this->dispatch('toastr', type: 'error', message: 'يجب السماح بتحديد الموقع قبل تسجيل بصمة الدخول.');

            return;
        }

        $data = [
            'log' => $now->format('H:i'),
            'check_in' => $now->format('H:i:s'),
        ];

        $data = array_merge($data, $this->locationData($location, 'check_in'));

        $fingerprint->fill($data)->save();

        $this->loadTodayFingerprint();
        $this->dispatch('toastr', type: 'success', message: __('Attendance recorded successfully.'));
    }

    public function clockOut(?array $location = null)
    {
        if (! $this->canUseSelfFingerprint()) {
            abort(403, 'Unauthorized action.');
        }

        $now = Carbon::now(self::OFFICIAL_TIMEZONE);
        $attendanceDate = $this->getCurrentAttendanceDate();
        $fingerprint = Fingerprint::where('employee_id', Auth::user()->employee_id)
            ->where('date', $attendanceDate)
            ->first();

        if (! $fingerprint || ! $fingerprint->check_in) {
            $this->dispatch('toastr', type: 'error', message: __('You need to record attendance first.'));

            return;
        }

        if ($fingerprint->check_out) {
            $this->dispatch('toastr', type: 'info', message: __('Departure has already been recorded for today.'));

            return;
        }

        if ($this->requiresAttendanceLocation() && ! $this->hasValidFingerprintLocation($location)) {
            $this->dispatch('toastr', type: 'error', message: 'يجب السماح بتحديد الموقع قبل تسجيل بصمة الخروج.');

            return;
        }

        $data = [
            'log' => Carbon::parse($fingerprint->check_in)->format('H:i').' '.$now->format('H:i'),
            'check_out' => $now->format('H:i:s'),
        ];

        $fingerprint->update(array_merge($data, $this->locationData($location, 'check_out')));

        $this->loadTodayFingerprint();
        $this->dispatch('toastr', type: 'success', message: __('Departure recorded successfully.'));
    }

    public function recordSystemLocation(?array $location = null): void
    {
        if (
            ! Auth::check()
            || ! Auth::user()->employee_id
            || ! Auth::user()->can('use attendance fingerprints')
            || ! $this->requiresAttendanceLocation()
            || ! $this->hasFingerprintLocationColumns()
            || ! $this->hasValidFingerprintLocation($location)
        ) {
            return;
        }

        $fingerprint = Fingerprint::firstOrNew([
            'employee_id' => Auth::user()->employee_id,
            'date' => $this->getCurrentAttendanceDate(),
        ]);

        if ($fingerprint->system_open_at) {
            return;
        }

        $fingerprint->fill(array_merge([
            'system_open_at' => Carbon::now(self::OFFICIAL_TIMEZONE),
        ], $this->locationData($location, 'system_open')))->save();

        $this->loadTodayFingerprint();
    }

    private function loadTodayFingerprint(): void
    {
        if (! Auth::check() || ! Auth::user()->employee_id) {
            $this->todayFingerprint = null;

            return;
        }

        $this->todayFingerprint = Fingerprint::where('employee_id', Auth::user()->employee_id)
            ->where('date', $this->getCurrentAttendanceDate())
            ->first();
    }

    private function loadTodayAttendanceRecords(): void
    {
        if (! $this->canViewTodayAttendanceRecords()) {
            $this->todayAttendanceRecords = collect();

            return;
        }

        $this->todayAttendanceRecords = Fingerprint::query()
            ->with('employee')
            ->where('date', $this->getCurrentAttendanceDate())
            ->whereNotNull('check_in')
            ->orderBy('check_in')
            ->get();
    }

    public function canViewTodayAttendanceRecords(): bool
    {
        return Auth::check()
            && (
                Auth::user()->can('view attendance')
                || Auth::user()->can('view attendance fingerprints')
                || $this->canEditTodayAttendance()
                || $this->canDeleteTodayAttendance()
            );
    }

    public function canEditTodayAttendance(): bool
    {
        return Auth::check()
            && (
                Auth::user()->can('manage attendance')
                || Auth::user()->can('manage attendance fingerprints')
                || Auth::user()->can('edit attendance fingerprints')
            );
    }

    public function canDeleteTodayAttendance(): bool
    {
        return Auth::check()
            && (
                Auth::user()->can('manage attendance')
                || Auth::user()->can('manage attendance fingerprints')
                || Auth::user()->can('delete attendance fingerprints')
            );
    }

    public function canOpenCreateMenu(): bool
    {
        return Auth::check()
            && (
                Auth::user()->can('create employees')
                || $this->canCreateAttendanceFingerprintRecord()
                || $this->canCreateAttendanceLeaves()
            );
    }

    public function canCreateAttendanceFingerprintRecord(): bool
    {
        return Auth::check()
            && (
                Auth::user()->can('manage attendance')
                || Auth::user()->can('manage attendance fingerprints')
                || Auth::user()->can('create attendance fingerprints')
            );
    }

    public function canManageAttendanceLeaves(): bool
    {
        return Auth::check()
            && (
                Auth::user()->can('manage attendance')
                || Auth::user()->can('manage attendance leaves')
            );
    }

    public function canCreateAttendanceLeaves(): bool
    {
        return $this->canManageAttendanceLeaves()
            || Auth::user()?->can('create attendance leaves');
    }

    public function canEditAttendanceLeaves(): bool
    {
        return $this->canManageAttendanceLeaves()
            || Auth::user()?->can('edit attendance leaves');
    }

    public function canDeleteAttendanceLeaves(): bool
    {
        return $this->canManageAttendanceLeaves()
            || Auth::user()?->can('delete attendance leaves');
    }

    public function requiresAttendanceLocation(): bool
    {
        if (! Auth::check() || ! Auth::user()->employee_id) {
            return true;
        }

        if (! Schema::hasColumn('employees', 'requires_attendance_location')) {
            return true;
        }

        return Auth::user()->employee?->requires_attendance_location !== false;
    }

    private function locationData(?array $location, string $prefix): array
    {
        if (! $this->hasFingerprintLocationColumns()) {
            return [];
        }

        $latitude = $location['latitude'] ?? null;
        $longitude = $location['longitude'] ?? null;
        $accuracy = $location['accuracy'] ?? null;

        $data = [];

        if ($latitude !== null && $longitude !== null) {
            $data["{$prefix}_latitude"] = round((float) $latitude, 7);
            $data["{$prefix}_longitude"] = round((float) $longitude, 7);
            $data["{$prefix}_accuracy"] = $accuracy !== null ? round((float) $accuracy, 2) : null;
        }

        return array_filter($data, fn ($value) => $value !== null);
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
            && Schema::hasColumn('fingerprints', 'system_open_latitude');
    }

    public function showEditAttendanceModal(int $fingerprintId): void
    {
        abort_unless($this->canEditTodayAttendance(), 403);

        $fingerprint = Fingerprint::findOrFail($fingerprintId);

        $this->editingAttendanceId = $fingerprint->id;
        $this->attendanceForm = [
            'date' => $fingerprint->date,
            'checkIn' => $fingerprint->check_in ? Carbon::parse($fingerprint->check_in)->format('H:i') : null,
            'checkOut' => $fingerprint->check_out ? Carbon::parse($fingerprint->check_out)->format('H:i') : null,
        ];
    }

    public function updateAttendance(): void
    {
        abort_unless($this->canEditTodayAttendance(), 403);

        $this->validate(
            [
                'attendanceForm.date' => ['required', 'date'],
                'attendanceForm.checkIn' => ['required'],
                'attendanceForm.checkOut' => ['nullable'],
            ],
            [],
            [
                'attendanceForm.date' => __('Date'),
                'attendanceForm.checkIn' => __('Check In'),
                'attendanceForm.checkOut' => __('Check Out'),
            ]
        );

        $checkIn = $this->normalizeTimeValue($this->attendanceForm['checkIn']);
        $checkOut = $this->normalizeTimeValue($this->attendanceForm['checkOut']);

        Fingerprint::findOrFail($this->editingAttendanceId)->update([
            'date' => $this->attendanceForm['date'],
            'log' => trim($checkIn.' '.($checkOut ?? '')),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);

        $this->loadTodayAttendanceRecords();
        $this->loadTodayFingerprint();
        $this->dispatch('closeModal', elementId: '#attendanceEditModal');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function confirmDeleteAttendance(int $fingerprintId): void
    {
        abort_unless($this->canDeleteTodayAttendance(), 403);

        $this->confirmedAttendanceId = $fingerprintId;
    }

    public function deleteAttendance(): void
    {
        abort_unless($this->canDeleteTodayAttendance(), 403);

        Fingerprint::findOrFail($this->confirmedAttendanceId)->delete();

        $this->confirmedAttendanceId = null;
        $this->loadTodayAttendanceRecords();
        $this->loadTodayFingerprint();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    private function normalizeTimeValue(?string $time): ?string
    {
        if (! filled($time)) {
            return null;
        }

        return Carbon::parse($time)->format('H:i:s');
    }

    private function loadWeeklyHolidayNotice(): void
    {
        $this->weeklyHolidayNotice = null;

        if (
            ! Auth::check()
            || ! Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee'])
            || Auth::user()->hasRole('Admin')
            || ! $this->employee
            || $this->employee->weekly_holiday === null
        ) {
            return;
        }

        $today = Carbon::parse($this->getCurrentAttendanceDate(), self::OFFICIAL_TIMEZONE)->startOfDay();

        if ((int) $this->employee->weekly_holiday !== (int) $today->dayOfWeek) {
            return;
        }

        $this->weeklyHolidayNotice = [
            'day_name' => $this->getArabicWeekdayName((int) $today->dayOfWeek),
            'target_at_ms' => $today->copy()->addDay()->setTime(self::ATTENDANCE_DAY_START_HOUR, 0)->timestamp * 1000,
        ];
    }

    public function getCurrentAttendanceDate(): string
    {
        $now = Carbon::now(self::OFFICIAL_TIMEZONE);

        if ($now->hour < self::ATTENDANCE_DAY_END_HOUR) {
            return $now->copy()->subDay()->toDateString();
        }

        return $now->toDateString();
    }

    public function isAttendanceWindowOpen(): bool
    {
        $hour = Carbon::now(self::OFFICIAL_TIMEZONE)->hour;

        return $hour >= self::ATTENDANCE_DAY_START_HOUR || $hour < self::ATTENDANCE_DAY_END_HOUR;
    }

    private function getArabicWeekdayName(int $dayOfWeek): string
    {
        return [
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ][$dayOfWeek] ?? '';
    }

    public function updatedSelectedEmployeeId()
    {
        $employee = Employee::find($this->selectedEmployeeId);

        if ($employee) {
            $this->employeePhoto = $employee->profile_photo_path;
        } else {
            $this->reset('employeePhoto');
        }
    }

    public function sendPendingMessages()
    {
        $this->authorizeAccess();

        if ($this->messagesStatus['unsent'] != 0) {
            sendPendingMessages::dispatch();
            session()->flash('info', __('Let\'s go! Personal on their way!'));
        } else {
            $this->dispatch('toastr', type: 'info' /* , title: 'Done!' */, message: __('Everything has sent already!'));
        }
    }

    public function showCreateLeaveModal()
    {
        abort_unless($this->canCreateAttendanceLeaves(), 403);

        $this->dispatch('clearSelect2Values');
        $this->reset('newLeaveInfo', 'isEdit');
    }

    public function createLeave()
    {
        abort_unless($this->canCreateAttendanceLeaves(), 403);

        EmployeeLeave::firstOrCreate([
            'employee_id' => $this->selectedEmployeeId,
            'leave_id' => $this->newLeaveInfo['LeaveId'],
            'from_date' => $this->newLeaveInfo['fromDate'],
            'to_date' => $this->newLeaveInfo['toDate'],
            'start_at' => $this->newLeaveInfo['startAt'],
            'end_at' => $this->newLeaveInfo['endAt'],
            'note' => $this->newLeaveInfo['note'],
        ]);

        session()->flash('success', __('Success, record created successfully!'));
        $this->dispatch('scrollToTop');

        $this->dispatch('closeModal', elementId: '#leaveModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    public function showEditLeaveModal($id)
    {
        abort_unless($this->canEditAttendanceLeaves(), 403);

        $this->reset('newLeaveInfo');

        $this->isEdit = true;
        $this->employeeLeaveId = $id;

        $record = DB::table('employee_leave')
            ->where('id', $this->employeeLeaveId)
            ->first();

        $this->selectedEmployeeId = $record->employee_id;
        $this->newLeaveInfo = [
            'LeaveId' => $record->leave_id,
            'fromDate' => $record->from_date,
            'toDate' => $record->to_date,
            'startAt' => $record->start_at,
            'endAt' => $record->end_at,
            'note' => $record->note,
        ];

        $this->dispatch('setSelect2Values', employeeId: $this->selectedEmployeeId, leaveId: $record->leave_id);
    }

    public function updateLeave()
    {
        abort_unless($this->canEditAttendanceLeaves(), 403);

        EmployeeLeave::find($this->employeeLeaveId)->update([
            'employee_id' => $this->selectedEmployeeId,
            'leave_id' => $this->newLeaveInfo['LeaveId'],
            'from_date' => $this->newLeaveInfo['fromDate'],
            'to_date' => $this->newLeaveInfo['toDate'],
            'start_at' => $this->newLeaveInfo['startAt'],
            'end_at' => $this->newLeaveInfo['endAt'],
            'note' => $this->newLeaveInfo['note'],
        ]);

        session()->flash('success', __('Success, record updated successfully!'));
        $this->dispatch('scrollToTop');

        $this->dispatch('closeModal', elementId: '#leaveModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

        $this->reset('isEdit', 'newLeaveInfo');
    }

    public function submitLeave()
    {
        $this->normalizeLeaveTimeFields();

        $this->validate(
            [
                'selectedEmployeeId' => 'required',
                'newLeaveInfo.LeaveId' => 'required',
                'newLeaveInfo.fromDate' => 'required|date',
                'newLeaveInfo.toDate' => 'required|date',
            ],
            null,
            [
                'selectedEmployeeId' => 'Employee',
                'newLeaveInfo.LeaveId' => 'Type',
                'newLeaveInfo.fromDate' => 'From Date',
                'newLeaveInfo.toDate' => 'To Date',
            ]
        );

        if (
            substr($this->newLeaveInfo['LeaveId'], 1, 1) == 1 &&
            ($this->newLeaveInfo['startAt'] != null || $this->newLeaveInfo['endAt'] != null)
        ) {
            session()->flash('error', __('Can\'t add daily leave with time!'));
            $this->dispatch('closeModal', elementId: '#leaveModal');
            $this->dispatch('toastr', type: 'error' /* , title: 'Done!' */, message: __('Requires Attention!'));

            return;
        }

        if (
            substr($this->newLeaveInfo['LeaveId'], 1, 1) == 2 &&
            ($this->newLeaveInfo['startAt'] == null || $this->newLeaveInfo['endAt'] == null)
        ) {
            session()->flash('error', __('Can\'t add hourly leave without time!'));
            $this->dispatch('closeModal', elementId: '#leaveModal');
            $this->dispatch('toastr', type: 'error' /* , title: 'Done!' */, message: __('Requires Attention!'));

            return;
        }

        if (
            substr($this->newLeaveInfo['LeaveId'], 1, 1) == 2 &&
            $this->newLeaveInfo['fromDate'] != $this->newLeaveInfo['toDate'] &&
            $this->newLeaveInfo['LeaveId'] != '1210'
        ) {
            session()->flash('error', __('Hourly leave must be on the same day'));
            $this->dispatch('closeModal', elementId: '#leaveModal');
            $this->dispatch('toastr', type: 'error' /* , title: 'Done!' */, message: __('Requires Attention!'));

            return;
        }

        if ($this->newLeaveInfo['fromDate'] > $this->newLeaveInfo['toDate']) {
            session()->flash('error', __('Check the dates entered. "From Date" can not be greater than "To Date"'));
            $this->dispatch('closeModal', elementId: '#leaveModal');
            $this->dispatch('toastr', type: 'error' /* , title: 'Done!' */, message: __('Requires Attention!'));

            return;
        }

        if ($this->newLeaveInfo['startAt'] > $this->newLeaveInfo['endAt']) {
            session()->flash('error', __('Check the times entered. "Start At" can not be greater than "End To"'));
            $this->dispatch('closeModal', elementId: '#leaveModal');
            $this->dispatch('toastr', type: 'error' /* , title: 'Done!' */, message: __('Requires Attention!'));

            return;
        }

        $this->isEdit
            ? abort_unless($this->canEditAttendanceLeaves(), 403)
            : abort_unless($this->canCreateAttendanceLeaves(), 403);

        $this->isEdit ? $this->updateLeave() : $this->createLeave();
    }

    public function updatedNewLeaveInfoLeaveId($value): void
    {
        if (! $this->leaveRequiresTime($value)) {
            $this->newLeaveInfo['startAt'] = null;
            $this->newLeaveInfo['endAt'] = null;
        }
    }

    public function confirmDestroyLeave($id)
    {
        abort_unless($this->canDeleteAttendanceLeaves(), 403);

        $this->confirmedId = $id;
    }

    public function destroyLeave()
    {
        abort_unless($this->canDeleteAttendanceLeaves(), 403);

        EmployeeLeave::find($this->confirmedId)->delete();

        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
        $this->confirmedId = null;
    }

    public function getEmployeeName($id)
    {
        return Employee::find($id)->FullName;
    }

    public function getLeaveType($id)
    {
        return Leave::find($id)->name;
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

    public function getEmployeeDiscounts()
    {
        $employeeId = auth()->user()->employee_id;

        $discounts = Discount::where('employee_id', $employeeId)
            ->latest('date')
            ->latest('id')
            ->take(8)
            ->get();

        $this->latestBatch = null;
        $this->batchDates = [];

        return $discounts;
    }

    private function authorizeAccess()
    {
        if (
            ! auth()
                ->user()
                ->hasAnyRole(['Admin', 'HR', 'CC', 'CR', 'CR-S'])
        ) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function normalizeLeaveTimeFields(): void
    {
        if (! $this->leaveRequiresTime($this->newLeaveInfo['LeaveId'] ?? null)) {
            $this->newLeaveInfo['startAt'] = null;
            $this->newLeaveInfo['endAt'] = null;
        }
    }

    private function leaveRequiresTime($leaveId): bool
    {
        return substr((string) $leaveId, 1, 1) == '2';
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

        if (Auth::user()->hasRole('Admin')) {
            return;
        }

        $now = Carbon::now(self::OFFICIAL_TIMEZONE);
        if (Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee', 'Viewer'])) {
            $this->collectEmployeeLeaveNotice($this->employee, $now);

            return;
        }

        Employee::query()
            ->where('is_active', 1)
            ->whereHas('leaves', function ($query) use ($now) {
                $query->where('employee_leave.to_date', '>=', $now->toDateString());
            })
            ->with([
                'leaves' => function ($query) use ($now) {
                    $query->where('employee_leave.to_date', '>=', $now->toDateString())
                        ->orderBy('employee_leave.from_date')
                        ->orderBy('employee_leave.start_at');
                },
            ])
            ->get()
            ->each(fn (Employee $employee) => $this->collectEmployeeLeaveNotice($employee, $now));
    }

    private function collectEmployeeLeaveNotice(Employee $employee, Carbon $now): void
    {
        foreach ($employee->leaves as $leave) {
            [$startAt, $endAt] = $this->resolveLeaveWindow($leave);

            if ($startAt <= $now && $endAt >= $now) {
                if (
                    ! $this->leaveNotice['active']
                    || $endAt->lt(Carbon::parse($this->leaveNotice['active']['end_at_iso']))
                ) {
                    $this->leaveNotice['active'] = $this->buildLeaveNoticeItem($leave, $startAt, $endAt, true, $employee);
                }

                continue;
            }

            if ($startAt->gt($now)) {
                if (
                    ! $this->leaveNotice['upcoming']
                    || $startAt->lt(Carbon::parse($this->leaveNotice['upcoming']['start_at_iso']))
                ) {
                    $this->leaveNotice['upcoming'] = $this->buildLeaveNoticeItem($leave, $startAt, $endAt, false, $employee);
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

    private function buildLeaveNoticeItem(Leave $leave, Carbon $startAt, Carbon $endAt, bool $isActive, ?Employee $employee = null): array
    {
        return [
            'leave_id' => $leave->id,
            'employee_name' => $employee?->full_name,
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

    private function loadManagementAlert(): void
    {
        if (! Schema::hasTable('admin_alerts')) {
            $this->managementAlert = null;

            return;
        }

        $alert = AdminAlert::query()->active()->latest()->first();

        $this->managementAlert = $alert ? [
            'body' => $alert->getLocalizedBody(),
            'updated_at' => $alert->updated_at,
        ] : null;
    }
}
