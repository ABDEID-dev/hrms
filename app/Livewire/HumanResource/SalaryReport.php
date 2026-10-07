<?php

namespace App\Livewire\HumanResource;

use App\Models\AccountTransaction;
use App\Models\Discount;
use App\Models\Employee;
use App\Models\Fingerprint;
use Carbon\Carbon;
use Livewire\Component;

class SalaryReport extends Component
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    private const PAYROLL_ACCOUNT = 'maktoom';

    public string $selectedMonth;

    public string $fromDate;

    public string $toDate;

    public string $search = '';

    public array $summary = [
        'employees_count' => 0,
        'gross_salaries' => 0,
        'withdrawals' => 0,
        'net_salaries' => 0,
        'cash_treasury' => 0,
        'cash_after_payroll' => 0,
        'attendance_days' => 0,
        'attendance_value' => 0,
    ];

    public array $rows = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('Admin'), 403);

        $this->selectedMonth = Carbon::now(self::OFFICIAL_TIMEZONE)->format('Y-m');
        $this->setMonthRange();
        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.human-resource.salary-report');
    }

    public function applyMonth(): void
    {
        $this->validate([
            'selectedMonth' => ['required', 'date_format:Y-m'],
        ]);

        $this->setMonthRange();
        $this->loadReport();
    }

    public function updatedSearch(): void
    {
        $this->loadReport();
    }

    private function setMonthRange(): void
    {
        $month = Carbon::createFromFormat('Y-m', $this->selectedMonth, self::OFFICIAL_TIMEZONE);
        $this->fromDate = $month->copy()->startOfMonth()->toDateString();
        $this->toDate = $month->copy()->endOfMonth()->toDateString();
    }

    private function loadReport(): void
    {
        $monthDays = Carbon::parse($this->fromDate, self::OFFICIAL_TIMEZONE)->daysInMonth;

        $employees = Employee::query()
            ->with('user.roles')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('father_name')
            ->orderBy('last_name')
            ->get()
            ->reject(fn (Employee $employee) => $employee->user?->hasRole('Admin'));

        $employeeIds = $employees->pluck('id')->all();

        $discounts = Discount::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$this->fromDate, $this->toDate])
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->groupBy('employee_id');

        $attendance = Fingerprint::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$this->fromDate, $this->toDate])
            ->where(function ($query) {
                $query->whereNotNull('log')
                    ->orWhereNotNull('check_in')
                    ->orWhereNotNull('check_out');
            })
            ->get()
            ->groupBy('employee_id');

        $rows = $employees->map(function (Employee $employee) use ($discounts, $attendance, $monthDays) {
            $grossSalary = $this->employeeGrossSalary($employee);
            $dailySalary = $monthDays > 0 ? $grossSalary / $monthDays : 0;
            $employeeDiscounts = $discounts->get($employee->id, collect());
            $withdrawals = (float) $employeeDiscounts->sum('rate');
            $discountDetails = $employeeDiscounts
                ->map(fn (Discount $discount) => [
                    'date' => $discount->date,
                    'reason' => $discount->reason ?: '---',
                    'amount' => (float) $discount->rate,
                    'is_auto' => (bool) $discount->is_auto,
                    'batch' => $discount->batch,
                ])
                ->values()
                ->all();
            $attendanceDays = $attendance->get($employee->id, collect())
                ->pluck('date')
                ->unique()
                ->count();
            $attendanceValue = $dailySalary * $attendanceDays;

            return [
                'id' => $employee->id,
                'name' => $employee->full_name ?: $employee->first_name ?: ('#'.$employee->id),
                'position' => $employee->current_position,
                'basic_salary' => (float) ($employee->basic_salary ?? 0),
                'housing_allowance' => (float) ($employee->housing_allowance ?? 0),
                'transportation_allowance' => (float) ($employee->transportation_allowance ?? 0),
                'gross_salary' => $grossSalary,
                'daily_salary' => $dailySalary,
                'attendance_days' => $attendanceDays,
                'attendance_value' => $attendanceValue,
                'withdrawals' => $withdrawals,
                'withdrawals_count' => $employeeDiscounts->count(),
                'discount_details' => $discountDetails,
                'remaining_salary' => max($grossSalary - $withdrawals, 0),
            ];
        });

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $rows = $rows->filter(fn (array $row) => str_contains(
                mb_strtolower($row['name'].' '.$row['position']),
                $needle
            ));
        }

        $rows = $rows->sortBy('name')->values();
        $grossSalaries = (float) $rows->sum('gross_salary');
        $withdrawals = (float) $rows->sum('withdrawals');
        $netSalaries = (float) $rows->sum('remaining_salary');
        $cashTreasury = $this->cashTreasuryBalance();

        $this->rows = $rows->all();
        $this->summary = [
            'employees_count' => $rows->count(),
            'gross_salaries' => $grossSalaries,
            'withdrawals' => $withdrawals,
            'net_salaries' => $netSalaries,
            'cash_treasury' => $cashTreasury,
            'cash_after_payroll' => $cashTreasury - $netSalaries,
            'attendance_days' => (int) $rows->sum('attendance_days'),
            'attendance_value' => (float) $rows->sum('attendance_value'),
        ];
    }

    private function employeeGrossSalary(Employee $employee): float
    {
        return (float) ($employee->basic_salary ?? 0)
            + (float) ($employee->housing_allowance ?? 0)
            + (float) ($employee->transportation_allowance ?? 0);
    }

    private function cashTreasuryBalance(): float
    {
        $transactions = AccountTransaction::query()
            ->where('account', self::PAYROLL_ACCOUNT)
            ->whereDate('date', '<=', $this->toDate)
            ->get();

        $cashRevenues = (float) $transactions
            ->where('type', 'revenue')
            ->where('payment_method', 'cash')
            ->sum('amount');

        $expenses = (float) $transactions
            ->where('type', 'expense')
            ->sum('amount');

        return $cashRevenues - $expenses;
    }
}
