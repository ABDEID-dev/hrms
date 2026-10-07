<?php

namespace App\Livewire\Accounts;

use App\Models\AccountTransaction;
use App\Models\Discount;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PayrollPayments extends Component
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';
    private const PAYROLL_ACCOUNT = 'maktoom';

    public string $selectedMonth;

    public string $search = '';

    public $transferAmount = null;

    public string $transferNote = '';

    public array $summary = [
        'employees_count' => 0,
        'gross_salaries' => 0,
        'discounts' => 0,
        'required' => 0,
        'paid' => 0,
        'remaining' => 0,
        'month_transfers' => 0,
        'maktoom_treasury' => 0,
        'available' => 0,
    ];

    public array $rows = [];

    public array $payAmounts = [];

    public ?int $confirmedEmployeeId = null;

    public ?int $confirmedCancelEmployeeId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Admin', 'ManagementEmployee']), 403);

        $this->selectedMonth = Carbon::now(self::OFFICIAL_TIMEZONE)->format('Y-m');
        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.accounts.payroll-payments');
    }

    public function applyMonth(): void
    {
        $this->validate([
            'selectedMonth' => ['required', 'date_format:Y-m'],
        ]);

        $this->confirmedEmployeeId = null;
        $this->confirmedCancelEmployeeId = null;
        $this->payAmounts = [];
        $this->loadReport();
    }

    public function updatedSearch(): void
    {
        $this->loadReport();
    }

    public function addTransfer(): void
    {
        $this->validate([
            'selectedMonth' => ['required', 'date_format:Y-m'],
            'transferAmount' => ['required', 'numeric', 'min:0.01'],
            'transferNote' => ['nullable', 'string', 'max:2000'],
        ]);

        AccountTransaction::create([
            'account' => self::PAYROLL_ACCOUNT,
            'type' => 'revenue',
            'revenue_kind' => 'payroll_transfer',
            'payroll_month' => $this->selectedMonth,
            'date' => $this->payrollTransactionDate(),
            'amount' => round((float) $this->transferAmount, 2),
            'payment_method' => 'cash',
            'service' => 'Payroll transfer '.$this->selectedMonth,
            'note' => $this->transferNote ?: 'Payroll transfer for '.$this->selectedMonth,
        ]);

        $this->transferAmount = null;
        $this->transferNote = '';
        $this->loadReport();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function preparePay(int $employeeId): void
    {
        $row = $this->findRow($employeeId);

        if (! $row || $row['remaining'] <= 0) {
            return;
        }

        $this->payAmounts[$employeeId] = number_format((float) min($row['remaining'], max($this->summary['available'], 0)), 2, '.', '');
        $this->confirmedEmployeeId = $employeeId;
        $this->confirmedCancelEmployeeId = null;
    }

    public function payEmployee(int $employeeId): void
    {
        $row = $this->findRow($employeeId);

        if (! $row) {
            return;
        }

        $this->validate([
            'payAmounts.'.$employeeId => ['required', 'numeric', 'min:0.01'],
        ]);

        $amount = round((float) $this->payAmounts[$employeeId], 2);

        if ($amount > round((float) $row['remaining'], 2)) {
            $this->addError('payAmounts.'.$employeeId, 'المبلغ أكبر من الراتب المتبقي للموظف.');
            return;
        }

        if ($amount > round((float) $this->summary['available'], 2)) {
            $this->addError('payAmounts.'.$employeeId, 'المبلغ أكبر من الإجمالي المتاح للتحويل والخزنة.');
            return;
        }

        DB::transaction(function () use ($employeeId, $row, $amount) {
            AccountTransaction::create([
                'account' => self::PAYROLL_ACCOUNT,
                'type' => 'expense',
                'expense_kind' => 'cash_withdrawal',
                'employee_id' => $employeeId,
                'payroll_month' => $this->selectedMonth,
                'date' => $this->payrollTransactionDate(),
                'employee_name' => $row['name'],
                'service' => null,
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
                'has_invoice' => null,
                'withdrawn_to' => $row['name'],
                'note' => 'Salary payment for '.$row['name'].' - '.$this->selectedMonth,
            ]);
        });

        unset($this->payAmounts[$employeeId]);
        $this->confirmedEmployeeId = null;
        $this->confirmedCancelEmployeeId = null;
        $this->loadReport();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function cancelPay(): void
    {
        $this->confirmedEmployeeId = null;
    }

    public function confirmCancelSalaryPayment(int $employeeId): void
    {
        abort_unless($this->canCancelSalaryPayments(), 403);

        $row = $this->findRow($employeeId);

        if (! $row || $row['paid'] <= 0) {
            return;
        }

        $this->confirmedEmployeeId = null;
        $this->confirmedCancelEmployeeId = $employeeId;
    }

    public function cancelSalaryPayment(int $employeeId): void
    {
        abort_unless($this->canCancelSalaryPayments(), 403);
        abort_unless($this->confirmedCancelEmployeeId === $employeeId, 403);

        $payment = $this->latestSalaryPaymentForEmployee($employeeId);

        if (! $payment) {
            $this->confirmedCancelEmployeeId = null;
            $this->loadReport();

            return;
        }

        $payment->delete();

        $this->confirmedCancelEmployeeId = null;
        unset($this->payAmounts[$employeeId]);
        $this->loadReport();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function canCancelSalaryPayments(): bool
    {
        return auth()->user()?->hasRole('Admin') === true;
    }

    private function loadReport(): void
    {
        $month = Carbon::createFromFormat('Y-m', $this->selectedMonth, self::OFFICIAL_TIMEZONE);
        $fromDate = $month->copy()->startOfMonth()->toDateString();
        $toDate = $month->copy()->endOfMonth()->toDateString();

        $employees = Employee::query()
            ->with('user.roles')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('father_name')
            ->orderBy('last_name')
            ->get()
            ->reject(fn (Employee $employee) => $employee->user?->hasRole('Admin'));

        $employeeIds = $employees->pluck('id')->all();

        $this->syncPayrollTransactionsForMonth();
        $this->syncAdvanceDiscountDatesForMonth($fromDate, $toDate);

        $discounts = Discount::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->groupBy('employee_id');

        $payments = AccountTransaction::query()
            ->where('account', self::PAYROLL_ACCOUNT)
            ->where('type', 'expense')
            ->where('payroll_month', $this->selectedMonth)
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('expense_kind', ['salary', 'cash_withdrawal'])
            ->get()
            ->groupBy('employee_id');

        $allRows = $employees->map(function (Employee $employee) use ($discounts, $payments) {
            $gross = $this->employeeGrossSalary($employee);
            $employeeDiscounts = (float) $discounts->get($employee->id, collect())->sum('rate');
            $paid = (float) $payments->get($employee->id, collect())->sum('amount');
            $required = max($gross - $employeeDiscounts, 0);

            return [
                'id' => $employee->id,
                'name' => $employee->full_name ?: $employee->first_name ?: ('#'.$employee->id),
                'position' => $employee->current_position,
                'gross' => $gross,
                'discounts' => $employeeDiscounts,
                'required' => $required,
                'paid' => $paid,
                'remaining' => max($required - $paid, 0),
            ];
        });

        $summaryRows = $allRows->filter(fn (array $row) => $row['remaining'] > 0 || $row['paid'] > 0);
        $rows = $summaryRows;

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $rows = $rows->filter(fn (array $row) => str_contains(mb_strtolower($row['name'].' '.$row['position']), $needle));
        }

        $rows = $rows->filter(fn (array $row) => $row['remaining'] > 0 || $row['paid'] > 0)->sortBy('name')->values();

        $monthTransfers = $this->monthTransfers();
        $paid = (float) $summaryRows->sum('paid');
        $maktoomTreasury = $this->maktoomTreasuryBalanceThrough($this->payrollTransactionDate());
        $available = $maktoomTreasury;

        $this->rows = $rows->all();
        $this->summary = [
            'employees_count' => $summaryRows->count(),
            'gross_salaries' => (float) $summaryRows->sum('gross'),
            'discounts' => (float) $summaryRows->sum('discounts'),
            'required' => (float) $summaryRows->sum('required'),
            'paid' => $paid,
            'remaining' => (float) $summaryRows->sum('remaining'),
            'month_transfers' => $monthTransfers,
            'maktoom_treasury' => $maktoomTreasury,
            'available' => $available,
        ];
    }

    private function findRow(int $employeeId): ?array
    {
        return collect($this->rows)->firstWhere('id', $employeeId);
    }

    private function latestSalaryPaymentForEmployee(int $employeeId): ?AccountTransaction
    {
        return AccountTransaction::query()
            ->where('account', self::PAYROLL_ACCOUNT)
            ->where('type', 'expense')
            ->where('payroll_month', $this->selectedMonth)
            ->where('employee_id', $employeeId)
            ->whereIn('expense_kind', ['salary', 'cash_withdrawal'])
            ->where('note', 'like', 'Salary payment for %')
            ->latest('date')
            ->latest('created_at')
            ->latest('id')
            ->first();
    }

    private function employeeGrossSalary(Employee $employee): float
    {
        return (float) ($employee->basic_salary ?? 0)
            + (float) ($employee->housing_allowance ?? 0)
            + (float) ($employee->transportation_allowance ?? 0);
    }

    private function payrollTransactionDate(): string
    {
        return Carbon::createFromFormat('Y-m', $this->selectedMonth, self::OFFICIAL_TIMEZONE)
            ->endOfMonth()
            ->toDateString();
    }

    private function syncPayrollTransactionsForMonth(): void
    {
        $payrollDate = $this->payrollTransactionDate();

        AccountTransaction::query()
            ->where('account', self::PAYROLL_ACCOUNT)
            ->where('payroll_month', $this->selectedMonth)
            ->where(function ($query) {
                $query->where(function ($nested) {
                    $nested->where('type', 'revenue')
                        ->where('revenue_kind', 'payroll_transfer');
                })->orWhere(function ($nested) {
                    $nested->where('type', 'expense')
                        ->whereIn('expense_kind', ['salary', 'cash_withdrawal'])
                        ->where('note', 'like', 'Salary payment for %');
                });
            })
            ->get()
            ->each(function (AccountTransaction $transaction) use ($payrollDate) {
                $updates = ['date' => $payrollDate];

                if ($transaction->type === 'expense') {
                    $updates = array_merge($updates, [
                        'expense_kind' => 'cash_withdrawal',
                        'service' => null,
                        'quantity' => 1,
                        'has_invoice' => null,
                        'withdrawn_to' => $transaction->withdrawn_to
                            ?: $transaction->employee_name
                            ?: $this->nameFromSalaryNote($transaction->note),
                    ]);
                }

                $transaction->forceFill($updates)->save();
            });
    }

    private function nameFromSalaryNote(?string $note): ?string
    {
        if (! $note || ! preg_match('/^Salary payment for (.+) - \d{4}-\d{2}$/u', $note, $matches)) {
            return null;
        }

        return $matches[1];
    }

    private function syncAdvanceDiscountDatesForMonth(string $fromDate, string $toDate): void
    {
        AccountTransaction::query()
            ->where('account', self::PAYROLL_ACCOUNT)
            ->where('type', 'expense')
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('date', [$fromDate, $toDate])
                    ->orWhere('payroll_month', $this->selectedMonth);
            })
            ->where(function ($query) {
                $query->where('expense_kind', 'advance')
                    ->orWhere('note', 'like', '%سلفة%')
                    ->orWhere('note', 'like', '%طلب رقم%')
                    ->orWhere('note', 'like', '%advance%');
            })
            ->get()
            ->each(function (AccountTransaction $transaction) {
                $date = Carbon::parse($transaction->date, self::OFFICIAL_TIMEZONE)->toDateString();
                $month = Carbon::parse($transaction->date, self::OFFICIAL_TIMEZONE)->format('Y-m');

                if ($transaction->payroll_month !== $month) {
                    $transaction->forceFill(['payroll_month' => $month])->save();
                }

                foreach ($this->advanceDiscountReasons($transaction) as $reason) {
                    $query = Discount::query()->where('reason', $reason);

                    if ($transaction->employee_id) {
                        $query->where('employee_id', $transaction->employee_id);
                    }

                    $query->update([
                        'date' => $date,
                        'batch' => $month,
                    ]);
                }
            });
    }

    private function advanceDiscountReasons(AccountTransaction $transaction): array
    {
        $note = (string) $transaction->note;
        $reasons = [];

        if (preg_match('/طلب رقم\s+(\d+)/u', $note, $matches)) {
            $reasons[] = __('ui.advance_salary_deduction_reason', ['id' => $matches[1]]);
        }

        if (str_contains($note, 'سلفة') && preg_match('/(\d+)/', $note, $matches)) {
            $reasons[] = __('ui.advance_salary_deduction_reason', ['id' => $matches[1]]);
        }

        $reasons[] = 'خصم سلفة موظف - مصروف رقم '.$transaction->id;

        return array_values(array_unique(array_filter($reasons)));
    }

    private function monthTransfers(): float
    {
        return (float) AccountTransaction::query()
            ->where('account', self::PAYROLL_ACCOUNT)
            ->where('type', 'revenue')
            ->where('revenue_kind', 'payroll_transfer')
            ->where('payroll_month', $this->selectedMonth)
            ->sum('amount');
    }

    private function maktoomTreasuryBalanceThrough(string $date): float
    {
        $transactions = AccountTransaction::query()
            ->where('account', self::PAYROLL_ACCOUNT)
            ->whereDate('date', '<=', $date)
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
