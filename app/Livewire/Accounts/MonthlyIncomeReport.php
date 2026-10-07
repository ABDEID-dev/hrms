<?php

namespace App\Livewire\Accounts;

use App\Livewire\Accounts\Concerns\AuthorizesAccountAccess;
use App\Livewire\Accounts\Concerns\UsesBusinessDay;
use App\Models\AccountTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class MonthlyIncomeReport extends Component
{
    use AuthorizesAccountAccess, UsesBusinessDay;

    public string $account;

    public string $accountName;

    public string $selectedMonth;

    public string $period = '3';

    public bool $excludePayrollTransfers = true;

    public string $paymentFilter = 'all';

    public string $revenueFilter = 'all';

    public array $availableMonths = [];

    public string $detailTitle = '';

    public array $detailRows = [];

    public float $detailTotal = 0.0;

    public int $serviceRowsLimit = 12;

    public array $periodOptions = [
        '1' => 'آخر شهر',
        '2' => 'آخر شهرين',
        '3' => 'آخر 3 شهور',
        '6' => 'آخر 6 شهور',
        '9' => 'آخر 9 شهور',
        '12' => 'آخر سنة',
    ];

    public array $paymentOptions = [
        'all' => 'كل طرق الدفع',
        'cash' => 'كاش فقط',
        'visa' => 'فيزا فقط',
    ];

    public array $revenueFilterOptions = [
        'all' => 'كل الدخل',
        'services' => 'الخدمات فقط',
        'products' => 'المنتجات فقط',
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
        $this->loadAvailableMonths();
        $this->selectedMonth = $this->defaultSelectedMonth();
    }

    public function render()
    {
        [$fromDate, $toDate, $months] = $this->reportRange();

        $transactions = AccountTransaction::query()
            ->with(['employee', 'inventoryProduct'])
            ->where('account', $this->account)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        $revenues = $transactions->where('type', 'revenue');
        $payrollTransfers = $revenues->filter(fn (AccountTransaction $transaction) => $this->isPayrollTransfer($transaction));
        $businessRevenues = $this->filteredBusinessRevenues($revenues);
        $expenses = $transactions->where('type', 'expense');

        return view('livewire.accounts.monthly-income-report', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'months' => $months,
            'totals' => $this->totals($businessRevenues, $expenses, $payrollTransfers),
            'monthlyRows' => $this->monthlyRows($months, $businessRevenues, $expenses, $payrollTransfers),
            'productRows' => $this->productRows($businessRevenues),
            'serviceRows' => $this->serviceRows($businessRevenues),
            'salaryRows' => $this->salaryRows($expenses),
            'expenseRows' => $this->expenseRows($expenses),
        ]);
    }

    public function updatedSelectedMonth(): void
    {
        if (! in_array($this->selectedMonth, $this->availableMonthKeys(), true)) {
            $this->selectedMonth = $this->defaultSelectedMonth();
        }

        $this->collapseServices();
    }

    public function updatedPeriod(): void
    {
        if (! array_key_exists($this->period, $this->periodOptions)) {
            $this->period = '3';
        }

        $this->collapseServices();
    }

    public function updatedPaymentFilter(): void
    {
        if (! array_key_exists($this->paymentFilter, $this->paymentOptions)) {
            $this->paymentFilter = 'all';
        }

        $this->collapseServices();
    }

    public function updatedRevenueFilter(): void
    {
        if (! array_key_exists($this->revenueFilter, $this->revenueFilterOptions)) {
            $this->revenueFilter = 'all';
        }

        $this->collapseServices();
    }

    public function showCurrentMonth(): void
    {
        $this->selectedMonth = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai')->format('Y-m');
        $this->loadAvailableMonths();
        $this->collapseServices();
    }

    public function showPreviousMonth(): void
    {
        $month = $this->adjacentAvailableMonth(-1);

        if ($month) {
            $this->selectedMonth = $month;
            $this->collapseServices();
        }
    }

    public function showNextMonth(): void
    {
        $month = $this->adjacentAvailableMonth(1);

        if ($month) {
            $this->selectedMonth = $month;
            $this->collapseServices();
        }
    }

    public function canShowPreviousMonth(): bool
    {
        return $this->adjacentAvailableMonth(-1) !== null;
    }

    public function canShowNextMonth(): bool
    {
        return $this->adjacentAvailableMonth(1) !== null;
    }

    public function showMoreServices(): void
    {
        $this->serviceRowsLimit += 12;
    }

    public function collapseServices(): void
    {
        $this->serviceRowsLimit = 12;
    }

    public function showTransactionDetails(string $title, string $ids): void
    {
        $transactionIds = collect(explode(',', $ids))
            ->map(fn (string $id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        $transactions = AccountTransaction::query()
            ->with(['employee', 'inventoryProduct'])
            ->where('account', $this->account)
            ->whereIn('id', $transactionIds)
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        $this->detailTitle = $title;
        $this->detailTotal = (float) $transactions->sum('amount');
        $this->detailRows = $transactions
            ->map(fn (AccountTransaction $transaction) => $this->transactionDetailRow($transaction))
            ->values()
            ->all();

        $this->dispatch('openModal', elementId: '#reportDetailsModal');
    }

    private function loadAvailableMonths(): void
    {
        $oldestDate = AccountTransaction::query()
            ->where('account', $this->account)
            ->min('date');

        $current = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai')->startOfMonth();
        $oldest = $oldestDate
            ? Carbon::parse($oldestDate, 'Asia/Dubai')->startOfMonth()
            : $current->copy();

        $months = [];
        for ($month = $current->copy(); $month->gte($oldest); $month->subMonth()) {
            $months[] = $month->format('Y-m');
        }

        $this->availableMonths = collect($months)
            ->map(fn (string $month) => [
                'value' => $month,
                'label' => Carbon::parse($month.'-01', 'Asia/Dubai')->locale('ar')->translatedFormat('F Y'),
            ])
            ->values()
            ->all();
    }

    private function defaultSelectedMonth(): string
    {
        $currentMonth = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai')->format('Y-m');

        return in_array($currentMonth, $this->availableMonthKeys(), true)
            ? $currentMonth
            : (string) ($this->availableMonths[0]['value'] ?? $currentMonth);
    }

    private function availableMonthKeys(): array
    {
        return collect($this->availableMonths)->pluck('value')->all();
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

    private function reportRange(): array
    {
        $periodMonths = max(1, (int) $this->period);
        $endMonth = Carbon::createFromFormat('Y-m', $this->selectedMonth, 'Asia/Dubai')->endOfMonth();
        $currentBusinessDate = Carbon::parse($this->getCurrentBusinessDate(), 'Asia/Dubai');

        if ($endMonth->gt($currentBusinessDate)) {
            $endMonth = $currentBusinessDate;
        }

        $startMonth = Carbon::parse($endMonth, 'Asia/Dubai')
            ->startOfMonth()
            ->subMonths($periodMonths - 1);

        $months = [];
        for ($month = $startMonth->copy(); $month->lte($endMonth); $month->addMonth()) {
            $months[] = $month->format('Y-m');
        }

        return [$startMonth->toDateString(), $endMonth->toDateString(), $months];
    }

    private function totals(Collection $revenues, Collection $expenses, Collection $payrollTransfers): array
    {
        $productRevenue = $this->productRevenues($revenues);
        $serviceRevenue = $this->serviceRevenues($revenues);
        $salaries = $expenses->filter(fn (AccountTransaction $transaction) => $this->isSalaryExpense($transaction));
        $totalRevenue = (float) $revenues->sum('amount');
        $totalExpenses = (float) $expenses->sum('amount');

        return [
            'revenue' => $totalRevenue,
            'cash' => (float) $revenues->where('payment_method', 'cash')->sum('amount'),
            'visa' => (float) $revenues->where('payment_method', 'visa')->sum('amount'),
            'product_revenue' => (float) $productRevenue->sum('amount'),
            'product_quantity' => (float) $productRevenue->sum(fn (AccountTransaction $transaction) => (float) ($transaction->quantity ?: 1)),
            'service_revenue' => (float) $serviceRevenue->sum('amount'),
            'service_count' => $serviceRevenue->count(),
            'expenses' => $totalExpenses,
            'purchases' => $this->sumExpenseKind($expenses, 'purchase'),
            'cash_withdrawals' => $this->sumNonSalaryExpenseKind($expenses, 'cash_withdrawal'),
            'tips' => $this->sumExpenseKind($expenses, 'tip'),
            'advances' => $this->sumExpenseKind($expenses, 'advance'),
            'salaries' => (float) $salaries->sum('amount'),
            'salary_count' => $salaries->count(),
            'payroll_transfers' => (float) $payrollTransfers->sum('amount'),
            'payroll_transfer_count' => $payrollTransfers->count(),
            'excluded_payroll_transfers' => $this->excludePayrollTransfers ? (float) $payrollTransfers->sum('amount') : 0.0,
            'net_income' => $totalRevenue - $totalExpenses,
        ];
    }

    private function monthlyRows(array $months, Collection $revenues, Collection $expenses, Collection $payrollTransfers): array
    {
        return collect($months)
            ->map(function (string $month) use ($revenues, $expenses, $payrollTransfers) {
                $monthRevenues = $revenues->filter(fn (AccountTransaction $transaction) => Carbon::parse($transaction->date)->format('Y-m') === $month);
                $monthExpenses = $expenses->filter(fn (AccountTransaction $transaction) => Carbon::parse($transaction->date)->format('Y-m') === $month);
                $monthTransfers = $payrollTransfers->filter(fn (AccountTransaction $transaction) => Carbon::parse($transaction->date)->format('Y-m') === $month);
                $salaryExpenses = $monthExpenses->filter(fn (AccountTransaction $transaction) => $this->isSalaryExpense($transaction));
                $productRevenue = $this->productRevenues($monthRevenues);
                $serviceRevenue = $this->serviceRevenues($monthRevenues);
                $revenueTotal = (float) $monthRevenues->sum('amount');
                $expensesTotal = (float) $monthExpenses->sum('amount');

                return [
                    'month' => $month,
                    'label' => Carbon::parse($month.'-01', 'Asia/Dubai')->locale('ar')->translatedFormat('F Y'),
                    'ids' => $monthRevenues->merge($monthTransfers)->merge($monthExpenses)->pluck('id')->unique()->implode(','),
                    'detail_title' => 'تفاصيل شهر '.Carbon::parse($month.'-01', 'Asia/Dubai')->locale('ar')->translatedFormat('F Y'),
                    'revenue' => $revenueTotal,
                    'cash' => (float) $monthRevenues->where('payment_method', 'cash')->sum('amount'),
                    'visa' => (float) $monthRevenues->where('payment_method', 'visa')->sum('amount'),
                    'products' => (float) $productRevenue->sum('amount'),
                    'product_quantity' => (float) $productRevenue->sum(fn (AccountTransaction $transaction) => (float) ($transaction->quantity ?: 1)),
                    'services' => (float) $serviceRevenue->sum('amount'),
                    'service_count' => $serviceRevenue->count(),
                    'expenses' => $expensesTotal,
                    'purchases' => $this->sumExpenseKind($monthExpenses, 'purchase'),
                    'cash_withdrawals' => $this->sumNonSalaryExpenseKind($monthExpenses, 'cash_withdrawal'),
                    'tips' => $this->sumExpenseKind($monthExpenses, 'tip'),
                    'advances' => $this->sumExpenseKind($monthExpenses, 'advance'),
                    'salaries' => (float) $salaryExpenses->sum('amount'),
                    'salary_count' => $salaryExpenses->count(),
                    'payroll_transfers' => (float) $monthTransfers->sum('amount'),
                    'net_income' => $revenueTotal - $expensesTotal,
                ];
            })
            ->values()
            ->all();
    }

    private function productRows(Collection $revenues): array
    {
        return $this->productRevenues($revenues)
            ->groupBy(function (AccountTransaction $transaction) {
                $quantity = max((float) ($transaction->quantity ?: 1), 0.000001);
                $unitPrice = (float) ($transaction->unit_price ?: ((float) $transaction->amount / $quantity));

                return ($transaction->inventory_product_id ?: 'manual').'-'.number_format($unitPrice, 2, '.', '');
            })
            ->map(function (Collection $rows) {
                $first = $rows->first();
                $quantity = (float) $rows->sum(fn (AccountTransaction $transaction) => (float) ($transaction->quantity ?: 1));
                $firstQuantity = max((float) ($first?->quantity ?: 1), 0.000001);

                return [
                    'name' => $this->productLabel($first),
                    'ids' => $rows->pluck('id')->implode(','),
                    'detail_title' => 'تفاصيل المنتج: '.$this->productLabel($first),
                    'sku' => $first?->inventoryProduct?->sku ?: '---',
                    'unit' => $first?->inventoryProduct?->unit ?: 'piece',
                    'quantity' => $quantity,
                    'unit_price' => (float) ($first?->unit_price ?: (($first?->amount ?: 0) / $firstQuantity)),
                    'total' => (float) $rows->sum('amount'),
                    'count' => $rows->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function serviceRows(Collection $revenues): array
    {
        return $this->serviceRevenues($revenues)
            ->groupBy(fn (AccountTransaction $transaction) => trim((string) ($transaction->service ?: 'خدمة')).'|'.number_format((float) $transaction->amount, 2, '.', ''))
            ->map(function (Collection $rows) {
                $first = $rows->first();

                return [
                    'service' => $first?->service ?: '---',
                    'ids' => $rows->pluck('id')->implode(','),
                    'detail_title' => 'تفاصيل الخدمة: '.($first?->service ?: '---'),
                    'employee' => $first?->employee_name ?: '---',
                    'price' => (float) ($first?->amount ?: 0),
                    'count' => $rows->count(),
                    'cash' => (float) $rows->where('payment_method', 'cash')->sum('amount'),
                    'visa' => (float) $rows->where('payment_method', 'visa')->sum('amount'),
                    'total' => (float) $rows->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function salaryRows(Collection $expenses): array
    {
        return $expenses
            ->filter(fn (AccountTransaction $transaction) => $this->isSalaryExpense($transaction))
            ->groupBy(fn (AccountTransaction $transaction) => ($transaction->payroll_month ?: Carbon::parse($transaction->date)->format('Y-m')).'|'.$this->salaryEmployeeName($transaction))
            ->map(function (Collection $rows) {
                $first = $rows->first();

                return [
                    'month' => $first?->payroll_month ?: Carbon::parse($first?->date)->format('Y-m'),
                    'employee' => $this->salaryEmployeeName($first),
                    'ids' => $rows->pluck('id')->implode(','),
                    'detail_title' => 'تفاصيل راتب: '.$this->salaryEmployeeName($first),
                    'paid' => (float) $rows->sum('amount'),
                    'count' => $rows->count(),
                ];
            })
            ->sortBy([['month', 'asc'], ['employee', 'asc']])
            ->values()
            ->all();
    }

    private function expenseRows(Collection $expenses): array
    {
        return collect(['purchase', 'cash_withdrawal', 'tip', 'advance', 'salary'])
            ->map(function (string $kind) use ($expenses) {
                $rows = $kind === 'salary'
                    ? $expenses->filter(fn (AccountTransaction $transaction) => $this->isSalaryExpense($transaction))
                    : ($kind === 'cash_withdrawal'
                        ? $expenses->filter(fn (AccountTransaction $transaction) => ($transaction->expense_kind ?: 'purchase') === $kind && ! $this->isSalaryExpense($transaction))
                        : $expenses->filter(fn (AccountTransaction $transaction) => ($transaction->expense_kind ?: 'purchase') === $kind));

                return [
                    'kind' => $kind,
                    'label' => $this->expenseKindLabel($kind),
                    'ids' => $rows->pluck('id')->implode(','),
                    'detail_title' => 'تفاصيل '.$this->expenseKindLabel($kind),
                    'total' => (float) $rows->sum('amount'),
                    'count' => $rows->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function productRevenues(Collection $revenues): Collection
    {
        return $revenues->filter(fn (AccountTransaction $transaction) => ($transaction->revenue_kind ?: 'service') === 'product');
    }

    private function serviceRevenues(Collection $revenues): Collection
    {
        return $revenues->filter(fn (AccountTransaction $transaction) => ($transaction->revenue_kind ?: 'service') !== 'product' && ! $this->isPayrollTransfer($transaction));
    }

    private function filteredBusinessRevenues(Collection $revenues): Collection
    {
        $rows = $this->excludePayrollTransfers
            ? $revenues->reject(fn (AccountTransaction $transaction) => $this->isPayrollTransfer($transaction))
            : $revenues;

        if ($this->paymentFilter !== 'all') {
            $rows = $rows->filter(fn (AccountTransaction $transaction) => ($transaction->payment_method ?: 'cash') === $this->paymentFilter);
        }

        if ($this->revenueFilter === 'products') {
            return $this->productRevenues($rows);
        }

        if ($this->revenueFilter === 'services') {
            return $this->serviceRevenues($rows);
        }

        return $rows;
    }

    private function isPayrollTransfer(AccountTransaction $transaction): bool
    {
        if ($transaction->type !== 'revenue') {
            return false;
        }

        if (($transaction->revenue_kind ?: null) === 'payroll_transfer') {
            return true;
        }

        $text = strtolower(collect([
            $transaction->service,
            $transaction->note,
            $transaction->withdrawn_to,
        ])->filter()->implode(' '));

        return str_contains($text, 'payroll transfer')
            || str_contains($text, 'payroll_transfer')
            || str_contains($text, 'salary transfer')
            || str_contains($text, 'تحويل الرواتب')
            || str_contains($text, 'تحويل مرتبات');
    }

    private function transactionDetailRow(AccountTransaction $transaction): array
    {
        $date = Carbon::parse($transaction->date, 'Asia/Dubai');
        $isRevenue = $transaction->type === 'revenue';
        $kind = $isRevenue
            ? (($transaction->revenue_kind ?: 'service') === 'product' ? 'منتج' : ($this->isPayrollTransfer($transaction) ? 'تحويل رواتب' : 'خدمة'))
            : $this->expenseKindLabel($transaction->expense_kind ?: 'purchase');
        $product = $transaction->inventoryProduct;
        $quantity = (float) ($transaction->quantity ?: 1);
        $unit = $product?->unit ?: 'piece';

        return [
            'id' => $transaction->id,
            'date' => $date->format('d-m-Y'),
            'day' => $date->locale('ar')->translatedFormat('l'),
            'time' => $transaction->created_at?->timezone('Asia/Dubai')->format('h:i A') ?: '---',
            'type' => $isRevenue ? 'دخل' : 'مصروف',
            'kind' => $kind,
            'description' => $this->transactionDescription($transaction),
            'employee' => $transaction->employee_name ?: $transaction->employee?->full_name ?: $transaction->withdrawn_to ?: '---',
            'payment_method' => $isRevenue
                ? ($transaction->payment_method === 'visa' ? 'فيزا' : 'كاش')
                : '---',
            'quantity' => $isRevenue && (($transaction->revenue_kind ?: 'service') === 'product')
                ? $this->quantityLabel($quantity, $unit)
                : '---',
            'unit_price' => (float) ($transaction->unit_price ?: 0),
            'amount' => (float) $transaction->amount,
            'customer' => $transaction->customer_name ?: '---',
            'note' => $transaction->note ?: '---',
        ];
    }

    private function transactionDescription(AccountTransaction $transaction): string
    {
        if (($transaction->revenue_kind ?: 'service') === 'product' && $transaction->inventoryProduct) {
            return $this->productLabel($transaction);
        }

        return $transaction->service
            ?: $transaction->description
            ?: $transaction->note
            ?: $transaction->withdrawn_to
            ?: '---';
    }

    private function sumExpenseKind(Collection $expenses, string $kind): float
    {
        return (float) $expenses
            ->filter(fn (AccountTransaction $transaction) => ($transaction->expense_kind ?: 'purchase') === $kind)
            ->sum('amount');
    }

    private function sumNonSalaryExpenseKind(Collection $expenses, string $kind): float
    {
        return (float) $expenses
            ->filter(fn (AccountTransaction $transaction) => ($transaction->expense_kind ?: 'purchase') === $kind && ! $this->isSalaryExpense($transaction))
            ->sum('amount');
    }

    private function isSalaryExpense(AccountTransaction $transaction): bool
    {
        if (($transaction->expense_kind ?: null) === 'salary') {
            return true;
        }

        return $transaction->type === 'expense'
            && filled($transaction->payroll_month)
            && str_contains((string) $transaction->note, 'Salary payment for');
    }

    private function salaryEmployeeName(?AccountTransaction $transaction): string
    {
        if (! $transaction) {
            return '---';
        }

        return $transaction->withdrawn_to
            ?: $transaction->employee?->full_name
            ?: $transaction->employee_name
            ?: '---';
    }

    private function productLabel(?AccountTransaction $transaction): string
    {
        $product = $transaction?->inventoryProduct;

        if (! $product) {
            return $transaction?->service ?: '---';
        }

        return collect([
            $product->name,
            $product->color,
            $product->length_cm ? $product->length_cm.' cm' : null,
            $product->sku ? 'SKU '.$product->sku : null,
        ])->filter()->implode(' - ');
    }

    public function quantityLabel(float $quantity, string $unit): string
    {
        $formatted = $unit === 'gram'
            ? number_format($quantity, 3)
            : number_format($quantity, 0);

        return $formatted.' '.($unit === 'gram' ? 'جرام' : 'قطعة');
    }

    public function expenseKindLabel(string $kind): string
    {
        return match ($kind) {
            'cash_withdrawal' => 'السحب النقدي',
            'tip' => 'التيبس / الإكراميات',
            'advance' => 'سلف الموظفين',
            'salary' => 'رواتب الموظفين',
            default => 'المشتريات',
        };
    }
}
