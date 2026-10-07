<?php

namespace App\Livewire\Accounts;

use App\Livewire\Accounts\Concerns\AuthorizesAccountAccess;
use App\Livewire\Accounts\Concerns\UsesBusinessDay;
use App\Models\AccountTransaction;
use App\Models\Employee;
use App\Models\InventoryMovement;
use App\Models\InventoryProduct;
use App\Support\AccountPermissions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Revenues extends Component
{
    use AuthorizesAccountAccess, UsesBusinessDay;

    public string $account;

    public string $accountName;

    public $revenues;

    public $selectedDayRevenues;

    public $employees;

    public string $selectedDate;

    public string $selectedMonth;

    public array $availableMonths = [];

    public array $dailySummary = [
        'total' => 0,
        'cash' => 0,
        'visa' => 0,
        'purchases' => 0,
        'cash_withdrawals' => 0,
        'tips' => 0,
        'advances' => 0,
        'expenses' => 0,
        'net_total' => 0,
        'treasury' => 0,
    ];

    public array $summary = [
        'total' => 0,
        'cash' => 0,
        'visa' => 0,
        'purchases' => 0,
        'cash_withdrawals' => 0,
        'tips' => 0,
        'advances' => 0,
        'expenses' => 0,
        'net_total' => 0,
        'treasury' => 0,
    ];

    public array $monthlyTreasuryDays = [];

    public ?int $confirmedRevenueId = null;

    public ?int $editingRevenueId = null;

    public string $viewingNote = '';

    public array $revenueForm = [
        'revenue_kind' => 'service',
        'inventory_product_id' => null,
        'employee_name' => '',
        'service' => '',
        'quantity' => 1,
        'unit_price' => null,
        'amount' => null,
        'payment_method' => 'cash',
        'note' => '',
        'customer_name' => '',
        'date' => null,
        'time' => null,
    ];

    private array $accountNames = [
        'maktoom' => 'Maktoum',
        'avani' => 'Avani',
        'perfumes' => 'Perfumes',
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
        $this->revenueForm['date'] = $this->getCurrentBusinessDate();
        $this->revenueForm['revenue_kind'] = $this->defaultRevenueKind();

        $this->loadEmployees();
        $this->loadRevenues();
    }

    public function render()
    {
        $inventoryProducts = InventoryProduct::query()
            ->where('account', $this->account)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('livewire.accounts.revenues', [
            'inventoryProducts' => $inventoryProducts,
        ]);
    }

    public function updatedRevenueFormInventoryProductId($value): void
    {
        if (! $value) {
            return;
        }

        $product = InventoryProduct::query()
            ->where('account', $this->account)
            ->find($value);

        if (! $product) {
            return;
        }

        $this->revenueForm['revenue_kind'] = 'product';
        $this->revenueForm['service'] = $this->productEnglishNameLabel($product);
        $this->revenueForm['unit_price'] = $product->unit_price;
        $this->revenueForm['amount'] = $product->unit_price;
        $this->revenueForm['quantity'] = $product->unit === 'piece' ? 1 : null;
    }

    public function updatedRevenueFormRevenueKind($value): void
    {
        if ($value === 'product') {
            $this->revenueForm['service'] = '';
            $this->revenueForm['amount'] = null;

            return;
        }

        $this->revenueForm['inventory_product_id'] = null;
        $this->revenueForm['quantity'] = 1;
        $this->revenueForm['unit_price'] = null;
    }

    public function createRevenue(): void
    {
        abort_unless($this->canCreateRevenue(), 403);

        if (! $this->canCreateBackdatedRevenue() && $this->businessWindowIsClosed()) {
            return;
        }

        $this->validateRevenueForm();
        $revenueDate = $this->createRevenueDate();

        DB::transaction(function () use ($revenueDate) {
            $transaction = AccountTransaction::create($this->transactionPayload($revenueDate));

            if ($this->productRevenue()) {
                $this->applyProductSale($transaction);
            }
        });

        $this->resetRevenueForm();
        $this->selectedDate = $revenueDate;
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->loadAvailableMonths();
        $this->loadRevenues();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function showEditRevenueModal(int $revenueId): void
    {
        $revenue = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->findOrFail($revenueId);

        if ($this->abortIfRevenueActionLocked($revenue, 'edit')) {
            return;
        }

        $this->editingRevenueId = $revenue->id;
        $this->revenueForm = [
            'revenue_kind' => $revenue->revenue_kind ?: 'service',
            'inventory_product_id' => $revenue->inventory_product_id,
            'employee_name' => $revenue->employee_name,
            'service' => $revenue->service,
            'quantity' => (float) ($revenue->quantity ?: 1),
            'unit_price' => $revenue->unit_price ?: $revenue->amount,
            'amount' => $revenue->amount,
            'payment_method' => $revenue->payment_method ?: 'cash',
            'note' => $revenue->note,
            'customer_name' => $revenue->customer_name,
            'date' => $revenue->date,
            'time' => $revenue->created_at?->timezone('Asia/Dubai')->format('H:i'),
        ];
    }

    public function updateRevenue(): void
    {
        $revenue = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->findOrFail($this->editingRevenueId);

        if ($this->abortIfRevenueActionLocked($revenue, 'edit')) {
            return;
        }

        $this->validateRevenueForm();

        DB::transaction(function () use ($revenue) {
            if (($revenue->revenue_kind ?: 'service') === 'product' && $revenue->inventory_product_id) {
                $this->reverseProductSale($revenue, 'Revenue edited');
            }

            $revenue->fill($this->transactionPayload($this->canEditRevenueDateTime() ? $this->normalizeRevenueDate() : $revenue->date));

            if ($this->canEditRevenueDateTime()) {
                $revenue->created_at = $this->normalizeRevenueDateTime();
            }

            $revenue->save();

            if ($this->productRevenue()) {
                $this->applyProductSale($revenue);
            }
        });

        $this->selectedDate = $revenue->date;
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->resetRevenueForm();
        $this->loadAvailableMonths();
        $this->loadRevenues();
        $this->dispatch('closeModal', elementId: '#revenueEditModal');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function showPreviousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->loadRevenues();
    }

    public function showNextDay(): void
    {
        if (! $this->canShowNextDay()) {
            return;
        }

        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->toDateString();
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->loadRevenues();
    }

    public function showCurrentBusinessDay(): void
    {
        $this->selectedDate = $this->getCurrentBusinessDate();
        $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
        $this->loadRevenues();
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
            $this->loadRevenues();
        } catch (\Throwable) {
            $this->selectedDate = $this->getCurrentBusinessDate();
            $this->selectedMonth = Carbon::parse($this->selectedDate, 'Asia/Dubai')->format('Y-m');
            $this->loadRevenues();
        }
    }

    public function canShowNextDay(): bool
    {
        return Carbon::parse($this->selectedDate)->lt(Carbon::parse($this->getCurrentBusinessDate()));
    }

    public function canShowPreviousDay(): bool
    {
        return true;
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

        $this->loadRevenues();
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

    public function selectedMonthStartDate(): string
    {
        return Carbon::parse($this->selectedMonth.'-01', 'Asia/Dubai')
            ->startOfMonth()
            ->toDateString();
    }

    public function selectedMonthEndDate(): string
    {
        return Carbon::parse($this->selectedMonth.'-01', 'Asia/Dubai')
            ->endOfMonth()
            ->toDateString();
    }

    public function selectedMonthSelectableEndDate(): string
    {
        $monthEnd = Carbon::parse($this->selectedMonthEndDate(), 'Asia/Dubai');
        $currentBusinessDate = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai');

        return $monthEnd->gt($currentBusinessDate)
            ? $currentBusinessDate->toDateString()
            : $monthEnd->toDateString();
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

    public function confirmDeleteRevenue(int $revenueId): void
    {
        $revenue = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->findOrFail($revenueId);

        if ($this->abortIfRevenueActionLocked($revenue, 'delete')) {
            return;
        }

        $this->confirmedRevenueId = $revenue->id;
    }

    public function showRevenueNote(int $revenueId): void
    {
        $revenue = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->findOrFail($revenueId);

        $this->viewingNote = $revenue->note ?: '---';
        $this->dispatch('openModal', elementId: '#revenueNoteModal');
    }

    public function deleteRevenue(): void
    {
        $revenue = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->findOrFail($this->confirmedRevenueId);

        if ($this->abortIfRevenueActionLocked($revenue, 'delete')) {
            return;
        }

        DB::transaction(function () use ($revenue) {
            if (($revenue->revenue_kind ?: 'service') === 'product' && $revenue->inventory_product_id) {
                $this->reverseProductSale($revenue, 'Revenue deleted');
            }

            $revenue->delete();
        });

        $this->confirmedRevenueId = null;
        $this->loadAvailableMonths();
        $this->loadRevenues();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    private function loadRevenues(): void
    {
        [$monthStart, $monthEnd] = $this->selectedMonthRange();

        $this->revenues = AccountTransaction::query()
            ->with('inventoryProduct')
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->tap(fn ($query) => $this->withoutPayrollTransfers($query))
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->latest('date')
            ->latest('created_at')
            ->latest('id')
            ->get();

        $this->selectedDayRevenues = AccountTransaction::query()
            ->with('inventoryProduct')
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->tap(fn ($query) => $this->withoutPayrollTransfers($query))
            ->where('date', $this->selectedDate)
            ->latest('created_at')
            ->latest('id')
            ->get();

        $dayCash = (float) $this->selectedDayRevenues->where('payment_method', 'cash')->sum('amount');
        $dayVisa = (float) $this->selectedDayRevenues->where('payment_method', 'visa')->sum('amount');
        $dayExpenseTransactions = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->tap(fn ($query) => $this->withoutSalaryPayments($query))
            ->where('date', $this->selectedDate)
            ->get();

        $dayPurchases = $this->sumExpensesByKind($dayExpenseTransactions, 'purchase');
        $dayCashWithdrawals = $this->sumExpensesByKind($dayExpenseTransactions, 'cash_withdrawal');
        $dayTips = $this->sumExpensesByKind($dayExpenseTransactions, 'tip');
        $dayAdvances = $this->sumExpensesByKind($dayExpenseTransactions, 'advance');
        $dayExpenses = $dayPurchases + $dayCashWithdrawals + $dayTips + $dayAdvances;
        $dayTreasury = $this->cashBalanceBetween($monthStart, $this->selectedDate);

        $summaryRevenues = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->tap(fn ($query) => $this->withoutPayrollTransfers($query))
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $cash = (float) $summaryRevenues->where('payment_method', 'cash')->sum('amount');
        $visa = (float) $summaryRevenues->where('payment_method', 'visa')->sum('amount');
        $expenseTransactions = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'expense')
            ->tap(fn ($query) => $this->withoutSalaryPayments($query))
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $purchases = $this->sumExpensesByKind($expenseTransactions, 'purchase');
        $cashWithdrawals = $this->sumExpensesByKind($expenseTransactions, 'cash_withdrawal');
        $tips = $this->sumExpensesByKind($expenseTransactions, 'tip');
        $advances = $this->sumExpensesByKind($expenseTransactions, 'advance');
        $expenses = $purchases + $cashWithdrawals + $tips + $advances;
        $treasuryBalanceDate = $this->treasuryBalanceDate($monthEnd);
        $monthlyTreasury = $this->cashBalanceBetween($monthStart, $treasuryBalanceDate);
        $this->monthlyTreasuryDays = $this->monthlyTreasuryDays($monthStart, $treasuryBalanceDate);

        $this->dailySummary = [
            'total' => $dayCash + $dayVisa,
            'cash' => $dayCash,
            'visa' => $dayVisa,
            'purchases' => $dayPurchases,
            'cash_withdrawals' => $dayCashWithdrawals,
            'tips' => $dayTips,
            'advances' => $dayAdvances,
            'expenses' => $dayExpenses,
            'net_total' => ($dayCash + $dayVisa) - $dayExpenses,
            'treasury' => $dayTreasury,
        ];

        $this->summary = [
            'total' => $cash + $visa,
            'cash' => $cash,
            'visa' => $visa,
            'purchases' => $purchases,
            'cash_withdrawals' => $cashWithdrawals,
            'tips' => $tips,
            'advances' => $advances,
            'expenses' => $expenses,
            'net_total' => ($cash + $visa) - $expenses,
            'treasury' => $monthlyTreasury,
        ];
    }

    private function monthlyTreasuryDays(string $monthStart, string $balanceDate): array
    {
        $start = Carbon::parse($monthStart, 'Asia/Dubai');
        $end = Carbon::parse($balanceDate, 'Asia/Dubai');
        $transactions = AccountTransaction::query()
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
            ->whereBetween('date', [$monthStart, $balanceDate])
            ->get()
            ->groupBy('date');

        $runningBalance = 0;
        $days = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateString = $date->toDateString();
            $dayTransactions = $transactions->get($dateString, collect());
            $cash = (float) $dayTransactions
                ->where('type', 'revenue')
                ->where('payment_method', 'cash')
                ->sum('amount');
            $expenses = (float) $dayTransactions
                ->where('type', 'expense')
                ->sum('amount');
            $netCash = $cash - $expenses;
            $runningBalance += $netCash;

            $days[] = [
                'date' => $dateString,
                'label' => $date->translatedFormat('l'),
                'cash' => $cash,
                'expenses' => $expenses,
                'net_cash' => $netCash,
                'balance' => $runningBalance,
            ];
        }

        return $days;
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

    private function validateRevenueForm(): void
    {
        $rules = [
            'revenueForm.revenue_kind' => ['required', 'in:service,product'],
            'revenueForm.service' => ['required', 'string', 'max:255'],
            'revenueForm.payment_method' => ['required', 'in:cash,visa'],
            'revenueForm.note' => ['nullable', 'string', 'max:2000'],
            'revenueForm.customer_name' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->productRevenue()) {
            $availableStock = $this->selectedProductAvailableStock();
            $rules['revenueForm.inventory_product_id'] = ['required', 'exists:inventory_products,id'];
            $rules['revenueForm.quantity'] = $this->selectedProductUnit() === 'piece'
                ? ['required', 'integer', 'min:1', 'max:'.floor((float) $availableStock)]
                : ['required', 'numeric', 'min:0.001', 'max:'.(float) $availableStock];
            $rules['revenueForm.unit_price'] = ['required', 'numeric', 'min:0.01'];
        } elseif ($this->isPerfumesAccount()) {
            $rules['revenueForm.quantity'] = ['required', 'integer', 'min:1'];
            $rules['revenueForm.unit_price'] = ['required', 'numeric', 'min:0.01'];
        } else {
            $rules['revenueForm.employee_name'] = ['required', 'string', 'max:255'];
            $rules['revenueForm.amount'] = ['required', 'numeric', 'min:0.01'];
        }

        if (! $this->editingRevenueId && $this->canCreateBackdatedRevenue()) {
            $rules['revenueForm.date'] = ['required', 'date', 'before_or_equal:'.$this->getCurrentBusinessDate()];
        }

        if ($this->editingRevenueId && $this->canEditRevenueDateTime()) {
            $rules['revenueForm.date'] = ['required', 'date', 'before_or_equal:'.$this->getCurrentBusinessDate()];
            $rules['revenueForm.time'] = ['required', 'regex:/^([01]?\d|2[0-3]):[0-5]\d(\s?[AP]M)?$/i'];
        }

        $this->validate($rules, [
            'revenueForm.quantity.max' => 'الكمية المطلوبة أكبر من المتاح في المخزون.',
            'revenueForm.date.before_or_equal' => 'لا يمكن إضافة إيراد بتاريخ بعد يوم العمل الحالي.',
        ]);
    }

    public function canEditRevenueDateTime(): bool
    {
        $user = Auth::user();

        return $user ? AccountPermissions::canEditRevenueDateTime($user, $this->account) : false;
    }

    public function canCreateBackdatedRevenue(): bool
    {
        $user = Auth::user();

        return $user ? AccountPermissions::canCreateBackdatedRevenue($user, $this->account) : false;
    }

    public function canCreateRevenue(): bool
    {
        $user = Auth::user();

        return $user ? AccountPermissions::canCreateRevenue($user, $this->account) : false;
    }

    public function canModifyRevenue(AccountTransaction $revenue): bool
    {
        $user = Auth::user();

        return $user
            ? AccountPermissions::canEditRevenue($user, $this->account, $revenue, $this->getCurrentBusinessDate(), $this->isBusinessWindowOpen())
            : false;
    }

    public function canDeleteRevenueRecord(AccountTransaction $revenue): bool
    {
        $user = Auth::user();

        return $user
            ? AccountPermissions::canDeleteRevenue($user, $this->account, $revenue, $this->getCurrentBusinessDate(), $this->isBusinessWindowOpen())
            : false;
    }

    public function canShowSelectedDayRevenueActions(): bool
    {
        return collect($this->selectedDayRevenues)->contains(
            fn (AccountTransaction $revenue): bool => $this->canModifyRevenue($revenue)
                || $this->canDeleteRevenueRecord($revenue)
        );
    }

    private function abortIfRevenueActionLocked(AccountTransaction $revenue, string $action): bool
    {
        $allowed = $action === 'delete'
            ? $this->canDeleteRevenueRecord($revenue)
            : $this->canModifyRevenue($revenue);

        if ($allowed) {
            return false;
        }

        $this->dispatch('toastr', type: 'error', message: __('accounts.record_locked'));

        return true;
    }

    public function isPerfumesAccount(): bool
    {
        return $this->account === 'perfumes';
    }

    public function productRevenue(): bool
    {
        return ($this->revenueForm['revenue_kind'] ?? 'service') === 'product';
    }

    public function revenueKindOptions(): array
    {
        $options = [
            'service' => $this->isPerfumesAccount() ? __('accounts.perfume_blend') : __('accounts.service'),
            'product' => __('accounts.inventory_product'),
        ];

        return $this->isPerfumesAccount()
            ? ['product' => $options['product'], 'service' => $options['service']]
            : $options;
    }

    public function selectedProductUnit(): ?string
    {
        if (! $this->productRevenue() || empty($this->revenueForm['inventory_product_id'])) {
            return null;
        }

        return InventoryProduct::query()
            ->where('account', $this->account)
            ->whereKey($this->revenueForm['inventory_product_id'])
            ->value('unit');
    }

    public function selectedProductAvailableStock(): ?float
    {
        if (! $this->productRevenue() || empty($this->revenueForm['inventory_product_id'])) {
            return null;
        }

        $product = InventoryProduct::query()
            ->where('account', $this->account)
            ->whereKey($this->revenueForm['inventory_product_id'])
            ->first();

        if (! $product) {
            return null;
        }

        return (float) $product->stock_quantity + $this->currentEditingSaleQuantityFor($product->id);
    }

    public function productOptionLabel(InventoryProduct $product): string
    {
        return collect([
            $this->productEnglishNameLabel($product),
            $this->productPriceLabel($product),
        ])->filter()->implode(' - ');
    }

    public function productEnglishNameLabel(InventoryProduct $product): string
    {
        $name = trim((string) $product->name);

        return collect([
            $name,
            $this->nameContains((string) $product->color, $name) ? null : $this->englishProductAttribute($product->color),
            $this->nameContains($product->length_cm ? $product->length_cm.' cm' : null, $name) ? null : ($product->length_cm ? $product->length_cm.' cm' : null),
            $product->sku ? 'SKU '.$product->sku : null,
        ])->filter()->implode(' - ');
    }

    public function productPriceLabel(InventoryProduct $product): string
    {
        return $product->unit_price !== null
            ? 'AED '.number_format((float) $product->unit_price, 2)
            : 'No price';
    }

    public function productNameLabel(InventoryProduct $product): string
    {
        $name = $this->shortArabicProductName($product);

        return collect([
            $name,
            $this->nameContains((string) $product->color, $name) ? null : $product->color,
            $this->nameContains($product->length_cm ? $product->length_cm.' سم' : null, $name) ? null : ($product->length_cm ? $product->length_cm.' سم' : null),
        ])->filter()->implode(' - ');
    }

    public function productQuantityLabel($quantity, ?InventoryProduct $product = null): string
    {
        $unit = $product?->unit ?? 'piece';
        $formatted = $this->formatProductQuantity($quantity, $unit);

        return $formatted.' '.$this->unitArabicLabel($unit, (float) $quantity);
    }

    private function shortArabicProductName(InventoryProduct $product): string
    {
        $name = trim((string) $product->name);

        if ($this->containsArabic($name)) {
            return $name;
        }

        $translated = $name;
        $replacements = [
            'Hair Care Mask' => 'ماسك شعر',
            'Conditioner' => 'بلسم',
            'Conditiner' => 'بلسم',
            'Shampoo Bar' => 'شامبو بار',
            'Shampoo' => 'شامبو',
            'Treatment' => 'تريتمنت',
            'Protector' => 'بروتكتور',
            'Proctctor' => 'بروتكتور',
            'Protctor' => 'بروتكتور',
            'Facial Cleanser' => 'غسول وجه',
            'Hand Cream' => 'كريم يد',
            'Oxidant' => 'أوكسيدان',
            'Recover' => 'ريكفر',
            'Hyaluronic' => 'هيالورونيك',
            'Surme' => 'سيروم',
            'Serum' => 'سيروم',
            'Vilot Lotion' => 'لوشن بنفسجي',
            'Violet Lavender' => 'بنفسجي لافندر',
            'White Silver' => 'أبيض فضي',
            'Whits Silver' => 'أبيض فضي',
            'Saffron copper' => 'زعفران نحاسي',
            'Saffron Copper' => 'زعفران نحاسي',
            'Cocoa Brown' => 'بني كاكاو',
            'Coca Brown' => 'بني كاكاو',
            'Coral Red' => 'أحمر مرجاني',
            'Frizz Control' => 'تحكم بالهيشان',
            'Body Maker' => 'بودي ميكر',
            'BodyMaker' => 'بودي ميكر',
            'Color Stay' => 'ثبات اللون',
            'Detox' => 'ديتوكس',
            'Full Defense' => 'حماية كاملة',
            'Deep Care' => 'عناية عميقة',
            'DEEP Care' => 'عناية عميقة',
            'Perfect Cleanse' => 'تنظيف عميق',
            'Organic Balance' => 'توازن عضوي',
            'Argan Oil' => 'زيت أرجان',
            'Argain' => 'أرجان',
            'Pure' => 'بيور',
            'Vital' => 'فيتال',
            'Relief' => 'ريليف',
            'Oil' => 'زيت',
        ];

        foreach ($replacements as $english => $arabic) {
            $translated = str_ireplace($english, $arabic, $translated);
        }

        return trim(preg_replace('/\s+/', ' ', $translated));
    }

    private function englishProductAttribute(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $translations = [
            'بني' => 'Brown',
            'بني فاتح' => 'Light Brown',
            'احمر' => 'Red',
            'أحمر' => 'Red',
            'وردي' => 'Pink',
            'اصفر' => 'Yellow',
            'أصفر' => 'Yellow',
            'رمادي' => 'Gray',
            'بيج' => 'Beige',
            'اخضر' => 'Green',
            'أخضر' => 'Green',
            'ابيض' => 'White',
            'أبيض' => 'White',
            'فاتح' => 'Light',
            'طبيعي' => 'Natural',
            'اسود' => 'Black',
            'أسود' => 'Black',
            'اشقر' => 'Blonde',
            'أشقر' => 'Blonde',
            'متنوع' => 'Mixed',
        ];

        return $translations[$value] ?? $value;
    }

    private function formatProductQuantity($quantity, string $unit): string
    {
        $formatted = number_format((float) $quantity, $unit === 'gram' ? 3 : 0);

        return $unit === 'gram'
            ? rtrim(rtrim($formatted, '0'), '.')
            : $formatted;
    }

    private function unitArabicLabel(string $unit, float $quantity = 1): string
    {
        if ($unit === 'gram') {
            return 'جرام';
        }

        return (int) $quantity === 1 ? 'قطعة' : 'قطع';
    }

    private function containsArabic(string $value): bool
    {
        return (bool) preg_match('/\p{Arabic}/u', $value);
    }

    private function nameContains(?string $needle, string $name): bool
    {
        if (! $needle) {
            return false;
        }

        return str_contains(mb_strtolower($name), mb_strtolower($needle));
    }

    private function currentEditingSaleQuantityFor(int $productId): float
    {
        if (! $this->editingRevenueId) {
            return 0;
        }

        $revenue = AccountTransaction::query()
            ->where('account', $this->account)
            ->where('type', 'revenue')
            ->whereKey($this->editingRevenueId)
            ->first();

        if (! $revenue || ($revenue->revenue_kind ?: 'service') !== 'product') {
            return 0;
        }

        return (int) $revenue->inventory_product_id === $productId
            ? (float) $revenue->quantity
            : 0;
    }

    private function normalizeRevenueDate(): string
    {
        return Carbon::parse($this->revenueForm['date'], 'Asia/Dubai')->toDateString();
    }

    private function createRevenueDate(): string
    {
        if (! $this->canCreateBackdatedRevenue()) {
            return $this->getCurrentBusinessDate();
        }

        return Carbon::parse($this->revenueForm['date'] ?: $this->getCurrentBusinessDate(), 'Asia/Dubai')->toDateString();
    }

    private function normalizeRevenueDateTime(): Carbon
    {
        return Carbon::parse($this->normalizeRevenueDate().' '.$this->revenueForm['time'], 'Asia/Dubai');
    }

    private function calculateRevenueAmount(): float
    {
        if ($this->productRevenue() || $this->isPerfumesAccount()) {
            return round((float) $this->revenueForm['quantity'] * (float) $this->revenueForm['unit_price'], 2);
        }

        return round((float) $this->revenueForm['amount'], 2);
    }

    private function resetRevenueForm(): void
    {
        $this->editingRevenueId = null;
        $this->revenueForm = [
            'revenue_kind' => $this->defaultRevenueKind(),
            'inventory_product_id' => null,
            'employee_name' => '',
            'service' => '',
            'quantity' => 1,
            'unit_price' => null,
            'amount' => null,
            'payment_method' => 'cash',
            'note' => '',
            'customer_name' => '',
            'date' => $this->getCurrentBusinessDate(),
            'time' => null,
        ];
    }

    private function defaultRevenueKind(): string
    {
        return $this->isPerfumesAccount() ? 'product' : 'service';
    }

    private function transactionPayload(string $date): array
    {
        return [
            'account' => $this->account,
            'type' => 'revenue',
            'revenue_kind' => $this->revenueForm['revenue_kind'],
            'inventory_product_id' => $this->productRevenue() ? $this->revenueForm['inventory_product_id'] : null,
            'date' => $date,
            'employee_name' => $this->isPerfumesAccount() || $this->productRevenue() ? null : $this->revenueForm['employee_name'],
            'service' => $this->revenueForm['service'],
            'quantity' => $this->quantityForTransaction(),
            'unit_price' => $this->unitPriceForTransaction(),
            'amount' => $this->calculateRevenueAmount(),
            'payment_method' => $this->revenueForm['payment_method'],
            'note' => $this->revenueForm['note'],
            'customer_name' => $this->revenueForm['customer_name'],
        ];
    }

    private function quantityForTransaction(): ?float
    {
        if ($this->productRevenue() || $this->isPerfumesAccount()) {
            return (float) $this->revenueForm['quantity'];
        }

        return null;
    }

    private function unitPriceForTransaction(): ?float
    {
        if ($this->productRevenue() || $this->isPerfumesAccount()) {
            return (float) $this->revenueForm['unit_price'];
        }

        return null;
    }

    private function applyProductSale(AccountTransaction $transaction): void
    {
        $product = InventoryProduct::query()
            ->where('account', $this->account)
            ->lockForUpdate()
            ->findOrFail($transaction->inventory_product_id);

        $quantity = (float) $transaction->quantity;

        if ((float) $product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'revenueForm.quantity' => 'الكمية المطلوبة أكبر من المتاح في المخزون.',
            ]);
        }

        $product->stock_quantity = (float) $product->stock_quantity - $quantity;
        $product->sold_quantity = (float) $product->sold_quantity + $quantity;
        $product->save();

        InventoryMovement::create([
            'inventory_product_id' => $product->id,
            'account' => $product->account,
            'type' => 'sale',
            'quantity' => -1 * $quantity,
            'unit_price' => $transaction->unit_price,
            'total_amount' => $transaction->amount,
            'balance_after' => $product->stock_quantity,
            'occurred_at' => $transaction->created_at ?: now(),
            'source_type' => AccountTransaction::class,
            'source_id' => $transaction->id,
            'note' => $transaction->customer_name ? 'Sold to '.$transaction->customer_name : 'Product revenue sale',
        ]);
    }

    private function reverseProductSale(AccountTransaction $transaction, string $reason): void
    {
        $product = InventoryProduct::query()
            ->lockForUpdate()
            ->find($transaction->inventory_product_id);

        if (! $product) {
            return;
        }

        $quantity = (float) $transaction->quantity;
        $product->stock_quantity = (float) $product->stock_quantity + $quantity;
        $product->sold_quantity = max(0, (float) $product->sold_quantity - $quantity);
        $product->save();

        InventoryMovement::create([
            'inventory_product_id' => $product->id,
            'account' => $product->account,
            'type' => 'adjustment',
            'quantity' => $quantity,
            'unit_price' => $transaction->unit_price,
            'total_amount' => $transaction->amount,
            'balance_after' => $product->stock_quantity,
            'occurred_at' => now(),
            'source_type' => AccountTransaction::class,
            'source_id' => $transaction->id,
            'note' => $reason,
        ]);
    }
}
