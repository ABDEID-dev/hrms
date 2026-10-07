<?php

namespace App\Livewire\Accounts;

use App\Livewire\Accounts\Concerns\AuthorizesAccountAccess;
use App\Livewire\Accounts\Concerns\UsesBusinessDay;
use App\Models\AccountTransaction;
use App\Models\Discount;
use App\Models\Employee;
use App\Support\AccountPermissions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class Expenses extends Component
{
    use AuthorizesAccountAccess, UsesBusinessDay, WithFileUploads;

    public string $account;

    public string $accountName;

    public $expenses;

    public $selectedDayExpenses;

    public $employees;

    public string $selectedDate;

    public string $selectedMonth;

    public array $availableMonths = [];

    public array $dailySummary = [
        'purchase' => 0,
        'cash_withdrawal' => 0,
        'tip' => 0,
        'advance' => 0,
        'total' => 0,
    ];

    public array $monthlySummary = [
        'cash' => 0,
        'purchase' => 0,
        'cash_withdrawal' => 0,
        'tip' => 0,
        'advance' => 0,
        'total' => 0,
        'treasury' => 0,
    ];

    public ?int $confirmedExpenseId = null;

    public ?int $editingExpenseId = null;

    public ?array $advanceEmployeeSalaryInfo = null;

    public $invoiceImage;

    public ?string $editingInvoiceImagePath = null;

    public array $expenseForm = [
        'expense_kind' => 'purchase',
        'employee_id' => null,
        'has_invoice' => '0',
        'withdrawn_to' => '',
        'service' => '',
        'quantity' => 1,
        'unit_price' => null,
        'note' => '',
        'date' => null,
        'time' => null,
    ];

    private array $accountNames = [
        'maktoom' => 'مكتوم',
        'avani' => 'افاني',
        'perfumes' => 'العطور',
    ];

    public function mount(string $account): void
    {
        abort_if(! array_key_exists($account, $this->accountNames), 404);
        $this->authorizeAccountAccess($account);

        $this->account = $account;
        $this->accountName = $this->accountNames[$account];
        $this->selectedDate = $this->getCurrentBusinessDate();
        $this->loadAvailableMonths();
        $this->selectedMonth = $this->defaultSelectedMonth();
        $this->selectedDate = $this->dateForMonth($this->selectedMonth);
        $this->expenseForm['date'] = $this->getCurrentBusinessDate();

        $this->loadEmployees();
        $this->loadExpenses();
        $this->loadAdvanceEmployeeSalaryInfo();
    }

    public function render()
    {
        return view('livewire.accounts.expenses');
    }

    public function createExpense(): void
    {
        abort_unless($this->canCreateExpense(), 403);

        if (! $this->canCreateBackdatedExpense() && $this->businessWindowIsClosed()) {
            return;
        }

        $this->validateExpenseForm();

        $amount = $this->calculateAmount();
        $expenseDate = $this->createExpenseDate();

        DB::transaction(function () use ($amount, $expenseDate) {
            $expense = AccountTransaction::create([
                'account' => $this->account,
                'type' => 'expense',
                'expense_kind' => $this->expenseForm['expense_kind'],
                'employee_id' => $this->advanceEmployeeId(),
                'payroll_month' => $this->expenseForm['expense_kind'] === 'advance' ? Carbon::parse($expenseDate, 'Asia/Dubai')->format('Y-m') : null,
                'date' => $expenseDate,
                'employee_name' => null,
                'service' => $this->expenseForm['expense_kind'] === 'purchase' ? $this->expenseForm['service'] : null,
                'quantity' => $this->expenseForm['expense_kind'] === 'purchase' ? $this->expenseForm['quantity'] : 1,
                'unit_price' => $this->expenseForm['unit_price'],
                'amount' => $amount,
                'has_invoice' => $this->expenseForm['expense_kind'] === 'purchase' ? (bool) $this->expenseForm['has_invoice'] : null,
                'withdrawn_to' => $this->expenseForm['expense_kind'] === 'advance'
                    ? $this->selectedAdvanceEmployeeName()
                    : ($this->usesWithdrawnTo() ? $this->expenseForm['withdrawn_to'] : null),
                'note' => $this->expenseForm['note'],
                'customer_name' => null,
            ]);

            $this->syncInvoiceImage($expense);
            $this->syncManualAdvanceDiscount($expense);
        });

        $this->resetExpenseForm();
        $this->selectedDate = $expenseDate;
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->loadAvailableMonths();
        $this->loadExpenses();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function showEditExpenseModal(int $expenseId): void
    {
        $expense = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->findOrFail($expenseId);

        if ($this->abortIfExpenseActionLocked($expense, 'edit')) {
            return;
        }

        $this->editingExpenseId = $expense->id;
        $this->expenseForm = [
            'expense_kind' => $expense->expense_kind ?: 'purchase',
            'employee_id' => $expense->employee_id,
            'has_invoice' => (string) (int) (bool) $expense->has_invoice,
            'withdrawn_to' => $expense->withdrawn_to,
            'service' => $expense->service,
            'quantity' => (int) ($expense->quantity ?: 1),
            'unit_price' => $expense->unit_price ?: $expense->amount,
            'note' => $expense->note,
            'date' => $expense->date,
            'time' => $expense->created_at?->timezone('Asia/Dubai')->format('H:i'),
        ];
        $this->editingInvoiceImagePath = $expense->invoice_image_path;
        $this->reset('invoiceImage');
    }

    public function updateExpense(): void
    {
        $expense = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->findOrFail($this->editingExpenseId);

        if ($this->abortIfExpenseActionLocked($expense, 'edit')) {
            return;
        }

        if ($this->canEditExpenseDateTime() && ! $this->normalizeExpenseDateTimeForm()) {
            return;
        }

        $wasAdvance = ($expense->expense_kind ?: 'purchase') === 'advance';
        if ($wasAdvance !== ($this->expenseForm['expense_kind'] === 'advance')) {
            $this->dispatch('toastr', type: 'error', message: 'السلفة تسجل كعملية جديدة فقط ولا يتم تحويل مصروف آخر إلى سلفة.');

            return;
        }

        $this->validateExpenseForm();

        DB::transaction(function () use ($expense, $wasAdvance) {
            if ($wasAdvance) {
                $this->deleteAdvanceDiscount($expense);
            }

            $expense->fill([
                'expense_kind' => $this->expenseForm['expense_kind'],
                'employee_id' => $this->advanceEmployeeId(),
                'payroll_month' => $this->expenseForm['expense_kind'] === 'advance' ? Carbon::parse($this->normalizeExpenseDate(), 'Asia/Dubai')->format('Y-m') : null,
                'employee_name' => null,
                'service' => $this->expenseForm['expense_kind'] === 'purchase' ? $this->expenseForm['service'] : null,
                'quantity' => $this->expenseForm['expense_kind'] === 'purchase' ? $this->expenseForm['quantity'] : 1,
                'unit_price' => $this->expenseForm['unit_price'],
                'amount' => $this->calculateAmount(),
                'has_invoice' => $this->expenseForm['expense_kind'] === 'purchase' ? (bool) $this->expenseForm['has_invoice'] : null,
                'withdrawn_to' => $this->expenseForm['expense_kind'] === 'advance'
                    ? $this->selectedAdvanceEmployeeName()
                    : ($this->usesWithdrawnTo() ? $this->expenseForm['withdrawn_to'] : null),
                'note' => $this->expenseForm['note'],
                'customer_name' => null,
            ]);

            if ($this->canEditExpenseDateTime()) {
                $expense->date = $this->normalizeExpenseDate();
                $expense->created_at = $this->normalizeExpenseDateTime();
            }

            $expense->save();
            $this->syncInvoiceImage($expense);

            if ($wasAdvance) {
                $this->syncManualAdvanceDiscount($expense->refresh());
            } else {
                $this->syncAdvancePayrollMetadata($expense->refresh());
            }
        });

        $this->selectedDate = $expense->date;
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->resetExpenseForm();
        $this->loadAvailableMonths();
        $this->loadExpenses();
        $this->dispatch('closeModal', elementId: '#expenseEditModal');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function confirmDeleteExpense(int $expenseId): void
    {
        $expense = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->findOrFail($expenseId);

        if ($this->abortIfExpenseActionLocked($expense, 'delete')) {
            return;
        }

        $this->confirmedExpenseId = $expense->id;
    }

    public function showPreviousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->loadExpenses();
    }

    public function updatedExpenseFormHasInvoice($value): void
    {
        if ((string) $value !== '1') {
            $this->reset('invoiceImage');
        }
    }

    public function updatedExpenseFormExpenseKind($value): void
    {
        if ($value !== 'purchase') {
            $this->reset('invoiceImage');
        }

        $this->loadAdvanceEmployeeSalaryInfo();
    }

    public function showNextDay(): void
    {
        if (! $this->canShowNextDay()) {
            return;
        }

        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->toDateString();
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->loadExpenses();
    }

    public function showCurrentBusinessDay(): void
    {
        $this->selectedDate = $this->getCurrentBusinessDate();
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->loadExpenses();
    }

    public function showPreviousMonth(): void
    {
        $month = $this->adjacentAvailableMonth(-1);

        if ($month) {
            $this->setSelectedMonth($month);
        }
    }

    public function showNextMonth(): void
    {
        if (! $this->canShowNextMonth()) {
            return;
        }

        $month = $this->adjacentAvailableMonth(1);

        if ($month) {
            $this->setSelectedMonth($month);
        }
    }

    public function showCurrentMonth(): void
    {
        $this->setSelectedMonth(Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai')->format('Y-m'));
    }

    public function updatedSelectedMonth(): void
    {
        $this->setSelectedMonth($this->selectedMonth);
    }

    public function updatedSelectedDate(): void
    {
        try {
            $this->selectedDate = Carbon::parse($this->selectedDate, 'Asia/Dubai')->toDateString();

            if (Carbon::parse($this->selectedDate)->gt(Carbon::parse($this->getCurrentBusinessDate()))) {
                $this->selectedDate = $this->getCurrentBusinessDate();
            }

            $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
            $this->loadExpenses();
            $this->loadAdvanceEmployeeSalaryInfo();
        } catch (\Throwable) {
            $this->selectedDate = $this->getCurrentBusinessDate();
            $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
            $this->loadExpenses();
            $this->loadAdvanceEmployeeSalaryInfo();
        }
    }

    public function updatedExpenseFormEmployeeId(): void
    {
        $this->loadAdvanceEmployeeSalaryInfo();
    }

    public function updatedExpenseFormDate(): void
    {
        $this->loadAdvanceEmployeeSalaryInfo();
    }

    public function canShowNextDay(): bool
    {
        return Carbon::parse($this->selectedDate)->lt(Carbon::parse($this->getCurrentBusinessDate()));
    }

    public function canShowNextMonth(): bool
    {
        return $this->adjacentAvailableMonth(1) !== null;
    }

    public function canShowPreviousMonth(): bool
    {
        return $this->adjacentAvailableMonth(-1) !== null;
    }

    private function setSelectedMonth(string $month): void
    {
        try {
            $selectedMonth = Carbon::createFromFormat('Y-m', $month, 'Asia/Dubai')->startOfMonth();
        } catch (\Throwable) {
            $selectedMonth = Carbon::parse($this->defaultSelectedMonth().'-01', 'Asia/Dubai')->startOfMonth();
        }

        if (! in_array($selectedMonth->format('Y-m'), $this->availableMonthKeys(), true)) {
            $selectedMonth = Carbon::parse($this->defaultSelectedMonth().'-01', 'Asia/Dubai')->startOfMonth();
        }

        $this->selectedMonth = $selectedMonth->format('Y-m');
        $this->selectedDate = $this->dateForMonth($this->selectedMonth);

        $this->loadExpenses();
        $this->loadAdvanceEmployeeSalaryInfo();
    }

    private function loadAvailableMonths(): void
    {
        $months = AccountTransaction::query()
            ->where('account', $this->account)
            ->where(function ($query) {
                $query->where(function ($nested) {
                    $nested->where('type', 'revenue');
                    $this->withoutPayrollTransfers($nested);
                })->orWhere(function ($nested) {
                    $nested->where('type', 'expense');
                    $this->withoutSalaryPayments($nested);
                });
            })
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date, 'Asia/Dubai')->format('Y-m'))
            ->filter()
            ->values()
            ->all();

        $currentMonth = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai')->format('Y-m');
        if (! in_array($currentMonth, $months, true)) {
            array_unshift($months, $currentMonth);
        }

        $this->availableMonths = collect($months)
            ->unique()
            ->sortDesc()
            ->map(fn (string $month) => [
                'value' => $month,
                'label' => Carbon::parse($month.'-01', 'Asia/Dubai')->locale('ar')->translatedFormat('F Y'),
            ])
            ->values()
            ->all();

        if (isset($this->selectedMonth) && ! in_array($this->selectedMonth, $this->availableMonthKeys(), true)) {
            $this->selectedMonth = $this->defaultSelectedMonth();
            $this->selectedDate = $this->dateForMonth($this->selectedMonth);
        }
    }

    private function availableMonthKeys(): array
    {
        return collect($this->availableMonths)->pluck('value')->all();
    }

    private function defaultSelectedMonth(): string
    {
        $currentMonth = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai')->format('Y-m');

        return in_array($currentMonth, $this->availableMonthKeys(), true)
            ? $currentMonth
            : (string) ($this->availableMonths[0]['value'] ?? $currentMonth);
    }

    private function dateForMonth(string $month): string
    {
        $currentBusinessDate = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai');
        $selectedMonth = Carbon::parse($month.'-01', 'Asia/Dubai');

        return $selectedMonth->isSameMonth($currentBusinessDate)
            ? $currentBusinessDate->toDateString()
            : $selectedMonth->toDateString();
    }

    private function adjacentAvailableMonth(int $direction): ?string
    {
        $keys = $this->availableMonthKeys();
        $index = array_search($this->selectedMonth, $keys, true);

        if ($index === false) {
            return null;
        }

        $targetIndex = $index - $direction;

        return $keys[$targetIndex] ?? null;
    }

    public function deleteExpense(): void
    {
        $expense = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->findOrFail($this->confirmedExpenseId);

        if ($this->abortIfExpenseActionLocked($expense, 'delete')) {
            return;
        }

        DB::transaction(function () use ($expense) {
            $this->deleteAdvanceDiscount($expense);
            $expense->delete();
        });

        $this->confirmedExpenseId = null;
        $this->loadAvailableMonths();
        $this->loadExpenses();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    private function loadExpenses(): void
    {
        [$monthStart, $monthEnd] = $this->selectedMonthRange();

        $this->expenses = AccountTransaction::query()
            ->with('employee')
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->tap(fn ($query) => $this->withoutSalaryPayments($query))
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->latest('date')
            ->latest('created_at')
            ->latest('id')
            ->get();

        $this->selectedDayExpenses = AccountTransaction::query()
            ->with('employee')
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->tap(fn ($query) => $this->withoutSalaryPayments($query))
            ->where('date', $this->selectedDate)
            ->latest('created_at')
            ->latest('id')
            ->get();

        $purchase = (float) $this->selectedDayExpenses
            ->filter(fn (AccountTransaction $expense) => ($expense->expense_kind ?: 'purchase') === 'purchase')
            ->sum('amount');
        $cashWithdrawal = (float) $this->selectedDayExpenses
            ->filter(fn (AccountTransaction $expense) => ($expense->expense_kind ?: 'purchase') === 'cash_withdrawal')
            ->sum('amount');
        $tip = (float) $this->selectedDayExpenses
            ->filter(fn (AccountTransaction $expense) => ($expense->expense_kind ?: 'purchase') === 'tip')
            ->sum('amount');
        $advance = (float) $this->selectedDayExpenses
            ->filter(fn (AccountTransaction $expense) => ($expense->expense_kind ?: 'purchase') === 'advance')
            ->sum('amount');

        $this->dailySummary = [
            'purchase' => $purchase,
            'cash_withdrawal' => $cashWithdrawal,
            'tip' => $tip,
            'advance' => $advance,
            'total' => $purchase + $cashWithdrawal + $tip + $advance,
        ];

        $monthlyPurchase = $this->sumExpensesByKind($this->expenses, 'purchase');
        $monthlyCashWithdrawal = $this->sumExpensesByKind($this->expenses, 'cash_withdrawal');
        $monthlyTip = $this->sumExpensesByKind($this->expenses, 'tip');
        $monthlyAdvance = $this->sumExpensesByKind($this->expenses, 'advance');
        $monthlyTotal = $monthlyPurchase + $monthlyCashWithdrawal + $monthlyTip + $monthlyAdvance;
        $monthlyCash = (float) AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->where('payment_method', 'cash')
            ->tap(fn ($query) => $this->withoutPayrollTransfers($query))
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->sum('amount');

        $this->monthlySummary = [
            'cash' => $monthlyCash,
            'purchase' => $monthlyPurchase,
            'cash_withdrawal' => $monthlyCashWithdrawal,
            'tip' => $monthlyTip,
            'advance' => $monthlyAdvance,
            'total' => $monthlyTotal,
            'treasury' => $this->cashBalanceBetween($monthStart, $this->treasuryBalanceDate($monthEnd)),
        ];
    }

    private function cashBalanceBetween(string $fromDate, string $toDate): float
    {
        $cash = (float) AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->where('payment_method', 'cash')
            ->tap(fn ($query) => $this->withoutPayrollTransfers($query))
            ->whereBetween('date', [$fromDate, $toDate])
            ->sum('amount');

        $expenses = (float) AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->tap(fn ($query) => $this->withoutSalaryPayments($query))
            ->whereBetween('date', [$fromDate, $toDate])
            ->sum('amount');

        return $cash - $expenses;
    }

    private function treasuryBalanceDate(string $monthEnd): string
    {
        $currentBusinessDate = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai');
        $balanceDate = Carbon::parse($monthEnd, 'Asia/Dubai');

        return $balanceDate->gt($currentBusinessDate)
            ? $currentBusinessDate->toDateString()
            : $balanceDate->toDateString();
    }

    private function selectedMonthRange(): array
    {
        $month = Carbon::parse($this->selectedMonth.'-01', 'Asia/Dubai');

        return [
            $month->copy()->startOfMonth()->toDateString(),
            $month->copy()->endOfMonth()->toDateString(),
        ];
    }

    private function sumExpensesByKind($transactions, string $kind): float
    {
        return (float) collect($transactions)
            ->filter(fn (AccountTransaction $transaction) => ($transaction->expense_kind ?: 'purchase') === $kind)
            ->sum('amount');
    }

    private function withoutPayrollTransfers($query): void
    {
        $query->where(function ($nested) {
            $nested->whereNull('revenue_kind')
                ->orWhere('revenue_kind', '!=', 'payroll_transfer');
        });
    }

    private function withoutSalaryPayments($query): void
    {
        $query
            ->where(function ($nested) {
                $nested->whereNull('expense_kind')
                    ->orWhere('expense_kind', '!=', 'salary');
            })
            ->where(function ($nested) {
                $nested->whereNull('note')
                    ->orWhere('note', 'not like', 'Salary payment for %');
            });
    }

    private function loadEmployees(): void
    {
        $this->employees = Employee::query()
            ->where('is_active', 1)
            ->orderBy('first_name')
            ->orderBy('father_name')
            ->orderBy('last_name')
            ->get();
    }

    private function validateExpenseForm(): void
    {
        $rules = [
            'expenseForm.expense_kind' => ['required', 'in:purchase,cash_withdrawal,tip,advance'],
            'expenseForm.unit_price' => ['required', 'numeric', 'min:0.01'],
            'expenseForm.note' => ['nullable', 'string', 'max:2000'],
        ];

        if ($this->expenseForm['expense_kind'] === 'advance') {
            if (! $this->isMaktoomAccount()) {
                throw ValidationException::withMessages([
                    'expenseForm.expense_kind' => 'السلفة متاحة في مصاريف مكتوم فقط.',
                ]);
            }

            $rules['expenseForm.employee_id'] = ['required', 'exists:employees,id'];

        } elseif ($this->usesWithdrawnTo()) {
            $rules['expenseForm.withdrawn_to'] = ['required', 'string', 'max:255'];
        } else {
            $rules['expenseForm.service'] = ['required', 'string', 'max:255'];
            $rules['expenseForm.has_invoice'] = ['required', 'boolean'];
            $rules['expenseForm.quantity'] = ['required', 'integer', 'min:1'];

            if ((bool) $this->expenseForm['has_invoice']) {
                $rules['invoiceImage'] = ['nullable', 'image', 'max:4096'];
            }
        }

        if (! $this->editingExpenseId && $this->canCreateBackdatedExpense()) {
            $rules['expenseForm.date'] = ['required', 'date', 'before_or_equal:'.$this->getCurrentBusinessDate()];
        }

        if ($this->editingExpenseId && $this->canEditExpenseDateTime()) {
            $rules['expenseForm.date'] = ['required', 'date', 'before_or_equal:'.$this->getCurrentBusinessDate()];
        }

        $this->validate($rules);

        if ($this->expenseForm['expense_kind'] === 'advance' && ! ($this->editingExpenseId && $this->isAccountAdmin())) {
            $this->loadAdvanceEmployeeSalaryInfo();

            $remainingSalary = (float) ($this->advanceEmployeeSalaryInfo['remaining'] ?? 0)
                + $this->editingAdvanceAmountCredit();
            if ((float) $this->expenseForm['unit_price'] > round($remainingSalary, 2)) {
                throw ValidationException::withMessages([
                    'expenseForm.unit_price' => 'مبلغ السلفة أكبر من المتبقي في راتب الموظف.',
                ]);
            }
        }
    }

    private function editingAdvanceAmountCredit(): float
    {
        if (! $this->editingExpenseId || ! $this->expenseForm['employee_id']) {
            return 0;
        }

        $expense = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->where('expense_kind', 'advance')
            ->find($this->editingExpenseId);

        return $expense && (int) $expense->employee_id === (int) $this->expenseForm['employee_id']
            ? (float) $expense->amount
            : 0;
    }

    private function normalizeExpenseDate(): string
    {
        return $this->parseExpenseDate($this->expenseForm['date'])->toDateString();
    }

    private function createExpenseDate(): string
    {
        if (! $this->canCreateBackdatedExpense()) {
            return $this->getCurrentBusinessDate();
        }

        return $this->parseExpenseDate($this->expenseForm['date'])->toDateString();
    }

    private function normalizeExpenseDateTime(): Carbon
    {
        return Carbon::createFromFormat(
            'Y-m-d H:i',
            $this->normalizeExpenseDate().' '.$this->normalizeExpenseTime($this->expenseForm['time']),
            'Asia/Dubai'
        );
    }

    private function normalizeExpenseDateTimeForm(): bool
    {
        try {
            $this->expenseForm['date'] = $this->normalizeExpenseDate();
            $this->expenseForm['time'] = $this->normalizeExpenseTime($this->expenseForm['time']);

            return true;
        } catch (\Throwable) {
            $this->dispatch('toastr', type: 'error', message: 'تأكد من كتابة التاريخ والوقت بشكل صحيح.');

            return false;
        }
    }

    private function parseExpenseDate(?string $value): Carbon
    {
        $value = trim((string) $value);

        foreach (['Y-m-d', 'm/d/Y', 'd-m-Y', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value, 'Asia/Dubai');

                if ($date !== false) {
                    return $date;
                }
            } catch (\Throwable) {
                //
            }
        }

        return Carbon::parse($value, 'Asia/Dubai');
    }

    private function normalizeExpenseTime(?string $value): string
    {
        $value = trim((string) $value);

        foreach (['H:i', 'H:i:s', 'h:i A', 'h:iA', 'g:i A', 'g:iA'] as $format) {
            try {
                $time = Carbon::createFromFormat($format, $value, 'Asia/Dubai');

                if ($time !== false) {
                    return $time->format('H:i');
                }
            } catch (\Throwable) {
                //
            }
        }

        return Carbon::parse($value, 'Asia/Dubai')->format('H:i');
    }

    public function canEditExpenseDateTime(): bool
    {
        $user = Auth::user();

        return $user ? AccountPermissions::canEditExpenseDateTime($user, $this->account) : false;
    }

    public function canCreateExpense(): bool
    {
        $user = Auth::user();

        return $user ? AccountPermissions::canCreateExpense($user, $this->account) : false;
    }

    public function canCreateBackdatedExpense(): bool
    {
        $user = Auth::user();

        return $user ? AccountPermissions::canCreateBackdatedExpense($user, $this->account) : false;
    }

    public function isPerfumesAccount(): bool
    {
        return $this->account === 'perfumes';
    }

    public function isMaktoomAccount(): bool
    {
        return $this->account === 'maktoom';
    }

    private function calculateAmount(): float
    {
        if ($this->usesWithdrawnTo()) {
            return round((float) $this->expenseForm['unit_price'], 2);
        }

        return round((float) $this->expenseForm['quantity'] * (float) $this->expenseForm['unit_price'], 2);
    }

    public function usesWithdrawnTo(): bool
    {
        return in_array($this->expenseForm['expense_kind'], ['cash_withdrawal', 'tip', 'advance'], true);
    }

    private function resetExpenseForm(): void
    {
        $this->editingExpenseId = null;
        $this->advanceEmployeeSalaryInfo = null;
        $this->editingInvoiceImagePath = null;
        $this->reset('invoiceImage');
        $this->expenseForm = [
            'expense_kind' => 'purchase',
            'employee_id' => null,
            'has_invoice' => '0',
            'withdrawn_to' => '',
            'service' => '',
            'quantity' => 1,
            'unit_price' => null,
            'note' => '',
            'date' => $this->getCurrentBusinessDate(),
            'time' => null,
        ];
    }

    private function syncInvoiceImage(AccountTransaction $expense): void
    {
        $hasInvoiceImage = $expense->expense_kind === 'purchase' && (bool) $expense->has_invoice;

        if (! $hasInvoiceImage) {
            $this->deleteInvoiceImage($expense->invoice_image_path);
            $expense->forceFill(['invoice_image_path' => null])->save();

            return;
        }

        if (! $this->invoiceImage) {
            return;
        }

        $oldPath = $expense->invoice_image_path;
        $newPath = $this->invoiceImage->store('account-invoices', 'public');

        $expense->forceFill(['invoice_image_path' => $newPath])->save();
        $this->deleteInvoiceImage($oldPath);
        $this->editingInvoiceImagePath = $newPath;
        $this->reset('invoiceImage');
    }

    private function deleteInvoiceImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function invoiceImageUrl(AccountTransaction $expense): ?string
    {
        return $expense->invoice_image_path
            ? Storage::disk('public')->url($expense->invoice_image_path)
            : null;
    }

    public function editingInvoiceImageUrl(): ?string
    {
        return $this->editingInvoiceImagePath
            ? Storage::disk('public')->url($this->editingInvoiceImagePath)
            : null;
    }

    public function canModifyExpense(AccountTransaction $expense): bool
    {
        $user = Auth::user();

        return $user
            ? AccountPermissions::canEditExpense($user, $this->account, $expense, $this->getCurrentBusinessDate(), $this->isBusinessWindowOpen())
            : false;
    }

    public function canDeleteExpense(AccountTransaction $expense): bool
    {
        $user = Auth::user();

        return $user
            ? AccountPermissions::canDeleteExpense($user, $this->account, $expense, $this->getCurrentBusinessDate(), $this->isBusinessWindowOpen())
            : false;
    }

    private function abortIfExpenseActionLocked(AccountTransaction $expense, string $action): bool
    {
        $allowed = $action === 'delete'
            ? $this->canDeleteExpense($expense)
            : $this->canModifyExpense($expense);

        if ($allowed) {
            return false;
        }

        $this->dispatch('toastr', type: 'error', message: __('accounts.record_locked'));

        return true;
    }

    private function advanceEmployeeId(): ?int
    {
        return $this->expenseForm['expense_kind'] === 'advance'
            ? (int) $this->expenseForm['employee_id']
            : null;
    }

    private function selectedAdvanceEmployeeName(): ?string
    {
        if ($this->expenseForm['expense_kind'] !== 'advance' || ! $this->expenseForm['employee_id']) {
            return null;
        }

        $employee = $this->employees->firstWhere('id', (int) $this->expenseForm['employee_id']);

        return $employee?->full_name ?: $employee?->first_name;
    }

    private function loadAdvanceEmployeeSalaryInfo(): void
    {
        if (($this->expenseForm['expense_kind'] ?? null) !== 'advance' || empty($this->expenseForm['employee_id'])) {
            $this->advanceEmployeeSalaryInfo = null;

            return;
        }

        $employee = $this->employees?->firstWhere('id', (int) $this->expenseForm['employee_id'])
            ?: Employee::find($this->expenseForm['employee_id']);

        if (! $employee) {
            $this->advanceEmployeeSalaryInfo = null;

            return;
        }

        $salaryDate = $this->getCurrentBusinessDate();
        $canChooseSalaryMonth = $this->editingExpenseId
            ? $this->canEditExpenseDateTime()
            : $this->canCreateBackdatedExpense();

        if ($canChooseSalaryMonth && filled($this->expenseForm['date'])) {
            try {
                $salaryDate = $this->parseExpenseDate($this->expenseForm['date'])->toDateString();
            } catch (\Throwable) {
                $salaryDate = $this->getCurrentBusinessDate();
            }
        }

        $month = Carbon::parse($salaryDate, 'Asia/Dubai');
        $fromDate = $month->copy()->startOfMonth()->toDateString();
        $toDate = $month->copy()->endOfMonth()->toDateString();
        $grossSalary = (float) ($employee->basic_salary ?? 0)
            + (float) ($employee->housing_allowance ?? 0)
            + (float) ($employee->transportation_allowance ?? 0);
        $discounts = (float) Discount::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->sum('rate');

        $this->advanceEmployeeSalaryInfo = [
            'gross' => $grossSalary,
            'discounts' => $discounts,
            'remaining' => max($grossSalary - $discounts, 0),
        ];
    }

    private function syncManualAdvanceDiscount(AccountTransaction $expense): void
    {
        if (($expense->expense_kind ?: 'purchase') !== 'advance' || ! $expense->employee_id) {
            return;
        }

        $note = $expense->note ?: 'تم صرف سلفة بواسطة المحل كاش - مصروف رقم '.$expense->id;
        $expense->forceFill([
            'note' => $note,
            'withdrawn_to' => $expense->withdrawn_to ?: $expense->employee?->full_name,
        ])->save();

        Discount::updateOrCreate(
            [
                'employee_id' => $expense->employee_id,
                'reason' => $this->advanceDiscountReason($expense),
            ],
            [
                'rate' => (int) round((float) $expense->amount),
                'date' => $expense->date,
                'is_auto' => false,
                'is_sent' => false,
                'batch' => Carbon::parse($expense->date, 'Asia/Dubai')->format('Y-m'),
            ]
        );
    }

    private function deleteAdvanceDiscount(AccountTransaction $expense): void
    {
        if (($expense->expense_kind ?: 'purchase') !== 'advance' || ! $expense->employee_id) {
            return;
        }

        Discount::query()
            ->where('employee_id', $expense->employee_id)
            ->where('reason', $this->advanceDiscountReason($expense))
            ->delete();
    }

    private function syncAdvancePayrollMetadata(AccountTransaction $expense): void
    {
        if (! $this->isAdvanceRelatedExpense($expense)) {
            return;
        }

        $date = Carbon::parse($expense->date, 'Asia/Dubai')->toDateString();
        $month = Carbon::parse($expense->date, 'Asia/Dubai')->format('Y-m');

        if ($expense->payroll_month !== $month) {
            $expense->forceFill(['payroll_month' => $month])->save();
        }

        foreach ($this->advanceDiscountReasons($expense) as $reason) {
            $query = Discount::query()->where('reason', $reason);

            if ($expense->employee_id) {
                $query->where('employee_id', $expense->employee_id);
            }

            $query->update([
                'date' => $date,
                'batch' => $month,
            ]);
        }
    }

    private function isAdvanceRelatedExpense(AccountTransaction $expense): bool
    {
        $note = (string) $expense->note;

        return ($expense->expense_kind ?: 'purchase') === 'advance'
            || str_contains($note, 'سلفة')
            || str_contains($note, 'طلب رقم')
            || str_contains(mb_strtolower($note), 'advance');
    }

    private function advanceDiscountReasons(AccountTransaction $expense): array
    {
        $reasons = [$this->advanceDiscountReason($expense)];
        $note = (string) $expense->note;

        if (preg_match('/طلب رقم\s+(\d+)/u', $note, $matches)) {
            $reasons[] = __('ui.advance_salary_deduction_reason', ['id' => $matches[1]]);
        }

        if (str_contains($note, 'سلفة') && preg_match('/(\d+)/', $note, $matches)) {
            $reasons[] = __('ui.advance_salary_deduction_reason', ['id' => $matches[1]]);
        }

        return array_values(array_unique(array_filter($reasons)));
    }

    private function advanceDiscountReason(AccountTransaction $expense): string
    {
        if (str_contains((string) $expense->note, 'طلب رقم')) {
            preg_match('/طلب رقم\s+(\d+)/u', (string) $expense->note, $matches);

            return __('ui.advance_salary_deduction_reason', ['id' => $matches[1] ?? $expense->id]);
        }

        return 'خصم سلفة موظف - مصروف رقم '.$expense->id;
    }
}
