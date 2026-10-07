<?php

namespace App\Livewire\Accounts;

use App\Models\AccountTransaction;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class EmployeeRevenues extends Component
{
    public string $selectedMonth;

    public string $fromDate;

    public string $toDate;

    public string $account = 'all';

    public string $search = '';

    public ?string $selectedEmployeeKey = null;

    public $employees;

    public array $rows = [];

    public array $totals = [
        'revenue' => 0,
        'tip' => 0,
        'balance' => 0,
    ];

    public $selectedTransactions;

    public array $accountLabels = [];

    public function mount(): void
    {
        $this->selectedMonth = now('Asia/Dubai')->format('Y-m');
        $this->setMonthRange();
        $this->accountLabels = User::accountBranches();
        $this->employees = collect();
        $this->selectedTransactions = collect();

        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.accounts.employee-revenues');
    }

    public function applyMonth(): void
    {
        $this->validate([
            'selectedMonth' => ['required', 'date_format:Y-m'],
        ]);

        $this->setMonthRange();
        $this->loadReport();
    }

    public function applyFilters(): void
    {
        $this->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date', 'after_or_equal:fromDate'],
            'account' => ['required', 'in:all,maktoom,avani,perfumes'],
        ]);

        $this->loadReport();
    }

    public function updatedSearch(): void
    {
        $this->loadReport();
    }

    public function selectEmployee(string $employeeKey): void
    {
        $this->selectedEmployeeKey = $employeeKey;
        $this->loadSelectedTransactions();
    }

    public function selectedEmployeeName(): string
    {
        return collect($this->rows)->firstWhere('key', $this->selectedEmployeeKey)['name'] ?? 'كل الموظفين';
    }

    private function setMonthRange(): void
    {
        $month = Carbon::createFromFormat('Y-m', $this->selectedMonth, 'Asia/Dubai');
        $this->fromDate = $month->copy()->startOfMonth()->toDateString();
        $this->toDate = $month->copy()->endOfMonth()->toDateString();
    }

    private function loadReport(): void
    {
        $this->employees = Employee::query()
            ->orderBy('first_name')
            ->orderBy('father_name')
            ->orderBy('last_name')
            ->get();

        $revenues = $this->transactionsQuery()
            ->where('type', 'revenue')
            ->whereNotNull('employee_name')
            ->get();

        $tips = $this->transactionsQuery()
            ->where('type', 'expense')
            ->where('expense_kind', 'tip')
            ->whereNotNull('withdrawn_to')
            ->get();

        $employeeRows = $this->employees->map(function (Employee $employee) use ($revenues, $tips) {
            $aliases = $this->employeeAliases($employee);

            $employeeRevenues = $revenues->filter(fn (AccountTransaction $transaction) => in_array(
                $this->normalizeName($transaction->employee_name),
                $aliases,
                true
            ));

            $employeeTips = $tips->filter(fn (AccountTransaction $transaction) => in_array(
                $this->normalizeName($transaction->withdrawn_to),
                $aliases,
                true
            ));

            return $this->makeRow(
                'employee-'.$employee->id,
                $employee->full_name ?: $employee->first_name ?: ('#'.$employee->id),
                $employeeRevenues,
                $employeeTips,
                $employee->current_position,
                $aliases
            );
        });

        $rows = $employeeRows
            ->when($this->search !== '', fn (Collection $rows) => $rows->filter(function (array $row) {
                return str_contains(
                    mb_strtolower($row['name'].' '.$row['position']),
                    mb_strtolower($this->search)
                );
            }))
            ->sortByDesc('balance')
            ->values();

        $this->rows = $rows->all();
        $this->totals = [
            'revenue' => (float) $rows->sum('revenue'),
            'tip' => (float) $rows->sum('tip'),
            'balance' => (float) $rows->sum('balance'),
        ];

        if ($this->selectedEmployeeKey && ! $rows->firstWhere('key', $this->selectedEmployeeKey)) {
            $this->selectedEmployeeKey = null;
        }

        $this->loadSelectedTransactions();
    }

    private function loadSelectedTransactions(): void
    {
        if (! $this->selectedEmployeeKey) {
            $this->selectedTransactions = collect();

            return;
        }

        $row = collect($this->rows)->firstWhere('key', $this->selectedEmployeeKey);

        if (! $row) {
            $this->selectedTransactions = collect();

            return;
        }

        $normalized = collect($row['aliases'] ?? [$row['name']])
            ->map(fn ($name) => $this->normalizeName($name))
            ->filter()
            ->unique()
            ->values();

        $this->selectedTransactions = $this->transactionsQuery()
            ->where(function ($query) {
                $query->where('type', 'revenue')
                    ->orWhere(function ($tipQuery) {
                        $tipQuery->where('type', 'expense')
                            ->where('expense_kind', 'tip');
                    });
            })
            ->orderBy('date')
            ->orderBy('created_at')
            ->get()
            ->filter(function (AccountTransaction $transaction) use ($normalized) {
                $name = $transaction->type === 'revenue' ? $transaction->employee_name : $transaction->withdrawn_to;

                return $normalized->contains($this->normalizeName($name));
            })
            ->values();
    }

    private function transactionsQuery()
    {
        return AccountTransaction::query()
            ->whereBetween('date', [$this->fromDate, $this->toDate])
            ->when($this->account !== 'all', fn ($query) => $query->where('account', $this->account));
    }

    private function makeRow(string $key, string $name, Collection $revenues, Collection $tips, ?string $position, array $aliases): array
    {
        $revenueTotal = (float) $revenues->sum('amount');
        $tipTotal = (float) $tips->sum('amount');

        return [
            'key' => $key,
            'name' => $name,
            'position' => $position ?: '---',
            'revenue' => $revenueTotal,
            'tip' => $tipTotal,
            'balance' => $revenueTotal + $tipTotal,
            'revenue_count' => $revenues->count(),
            'tip_count' => $tips->count(),
            'aliases' => $aliases,
        ];
    }

    private function employeeAliases(Employee $employee): array
    {
        return collect([
            $employee->full_name,
            $employee->short_name,
        ])
            ->map(fn ($name) => $this->normalizeName($name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeName(?string $name): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $name)));
    }
}
