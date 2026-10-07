<?php

namespace App\Livewire\HumanResource\Attendance;

use App\Models\Employee;
use App\Models\EmployeeLocationEvent;
use App\Models\Fingerprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class LocationReport extends Component
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    public string $fromDate;

    public string $toDate;

    public string $employeeId = '';

    public $employees;

    public $records;

    public $systemOpenEvents;

    public array $stats = [
        'records' => 0,
        'system_open' => 0,
        'check_in' => 0,
        'check_out' => 0,
        'system_open_events' => 0,
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasRole('Admin'), 403);

        $today = Carbon::now(self::OFFICIAL_TIMEZONE);
        $this->fromDate = $today->copy()->startOfMonth()->toDateString();
        $this->toDate = $today->toDateString();
        $this->loadEmployees();

        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.human-resource.attendance.location-report');
    }

    public function updatedFromDate(): void
    {
        $this->normalizeDates();
        $this->loadReport();
    }

    public function updatedToDate(): void
    {
        $this->normalizeDates();
        $this->loadReport();
    }

    public function updatedEmployeeId(): void
    {
        $this->loadReport();
    }

    public function resetToToday(): void
    {
        $today = Carbon::now(self::OFFICIAL_TIMEZONE)->toDateString();
        $this->fromDate = $today;
        $this->toDate = $today;

        $this->loadReport();
    }

    public function resetToThisMonth(): void
    {
        $today = Carbon::now(self::OFFICIAL_TIMEZONE);
        $this->fromDate = $today->copy()->startOfMonth()->toDateString();
        $this->toDate = $today->toDateString();

        $this->loadReport();
    }

    private function loadReport(): void
    {
        $this->normalizeDates();
        $employeeIds = $this->trackableEmployeeIds();

        if ($this->employeeId !== '' && ! in_array((int) $this->employeeId, $employeeIds, true)) {
            $this->employeeId = '';
        }

        if ($this->hasLocationColumns()) {
            $query = Fingerprint::query()
                ->with('employee')
                ->whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$this->fromDate, $this->toDate])
                ->where(function ($query) {
                    $query->whereNotNull('system_open_latitude')
                        ->orWhereNotNull('check_in_latitude')
                        ->orWhereNotNull('check_out_latitude');
                })
                ->when($this->employeeId !== '', fn ($query) => $query->where('employee_id', $this->employeeId))
                ->latest('date')
                ->latest('updated_at');

            $this->records = $query->get();
        } else {
            $this->records = collect();
        }

        $this->systemOpenEvents = $this->hasEventTable()
            ? EmployeeLocationEvent::query()
                ->with(['employee', 'user'])
                ->where('event_type', 'system_open')
                ->whereBetween('occurred_at', [
                    Carbon::parse($this->fromDate, self::OFFICIAL_TIMEZONE)->startOfDay(),
                    Carbon::parse($this->toDate, self::OFFICIAL_TIMEZONE)->endOfDay(),
                ])
                ->whereIn('employee_id', $employeeIds)
                ->when($this->employeeId !== '', fn ($query) => $query->where('employee_id', $this->employeeId))
                ->latest('occurred_at')
                ->take(300)
                ->get()
            : collect();

        $this->stats = [
            'records' => $this->records->count(),
            'system_open' => $this->records->whereNotNull('system_open_latitude')->count(),
            'check_in' => $this->records->whereNotNull('check_in_latitude')->count(),
            'check_out' => $this->records->whereNotNull('check_out_latitude')->count(),
            'system_open_events' => $this->systemOpenEvents->count(),
        ];
    }

    private function loadEmployees(): void
    {
        $this->employees = Employee::query()
            ->with('user.roles')
            ->where('is_active', 1)
            ->orderBy('first_name')
            ->orderBy('father_name')
            ->orderBy('last_name')
            ->get()
            ->reject(fn (Employee $employee) => $employee->user?->hasRole('Admin'))
            ->values();
    }

    private function trackableEmployeeIds(): array
    {
        if (! $this->employees) {
            $this->loadEmployees();
        }

        return $this->employees
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function normalizeDates(): void
    {
        $this->fromDate = $this->fromDate ?: Carbon::now(self::OFFICIAL_TIMEZONE)->startOfMonth()->toDateString();
        $this->toDate = $this->toDate ?: Carbon::now(self::OFFICIAL_TIMEZONE)->toDateString();

        if (Carbon::parse($this->fromDate)->greaterThan(Carbon::parse($this->toDate))) {
            [$this->fromDate, $this->toDate] = [$this->toDate, $this->fromDate];
        }
    }

    private function hasLocationColumns(): bool
    {
        return Schema::hasColumn('fingerprints', 'system_open_latitude')
            && Schema::hasColumn('fingerprints', 'check_in_latitude')
            && Schema::hasColumn('fingerprints', 'check_out_latitude');
    }

    private function hasEventTable(): bool
    {
        return Schema::hasTable('employee_location_events');
    }
}
