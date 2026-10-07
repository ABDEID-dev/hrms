<?php

namespace App\Livewire\Accounts;

use App\Livewire\Accounts\Concerns\AuthorizesAccountAccess;
use App\Models\AccountTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Treasury extends Component
{
    use AuthorizesAccountAccess;

    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    public string $account;

    public string $accountName;

    public string $fromDate;

    public string $toDate;

    public array $summary = [
        'opening_balance' => 0,
        'revenues' => 0,
        'cash_revenues' => 0,
        'visa_revenues' => 0,
        'purchases' => 0,
        'cash_withdrawals' => 0,
        'tips' => 0,
        'salaries' => 0,
        'expenses' => 0,
        'net_movement' => 0,
        'closing_balance' => 0,
    ];

    public Collection $dailyReports;

    public Collection $transactions;

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

        $today = Carbon::now(self::OFFICIAL_TIMEZONE);
        $this->fromDate = $today->copy()->startOfMonth()->toDateString();
        $this->toDate = $today->toDateString();

        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.accounts.treasury');
    }

    public function updatedFromDate(): void
    {
        $this->normalizeDateRange();
        $this->loadReport();
    }

    public function updatedToDate(): void
    {
        $this->normalizeDateRange();
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
        $this->normalizeDateRange();

        $from = Carbon::parse($this->fromDate, self::OFFICIAL_TIMEZONE)->toDateString();
        $to = Carbon::parse($this->toDate, self::OFFICIAL_TIMEZONE)->toDateString();

        $openingTransactions = AccountTransaction::query()
            ->where('account', $this->account)
            ->whereDate('date', '<', $from)
            ->get();

        $this->transactions = AccountTransaction::query()
            ->where('account', $this->account)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $openingBalance = $this->calculateNet($openingTransactions);
        $revenues = $this->sumByType($this->transactions, 'revenue');
        $cashRevenues = $this->sumRevenuesByPaymentMethod($this->transactions, 'cash');
        $visaRevenues = $this->sumRevenuesByPaymentMethod($this->transactions, 'visa');
        $purchases = $this->sumExpensesByKind($this->transactions, 'purchase');
        $cashWithdrawals = $this->sumExpensesByKind($this->transactions, 'cash_withdrawal');
        $tips = $this->sumExpensesByKind($this->transactions, 'tip');
        $salaries = $this->sumExpensesByKind($this->transactions, 'salary');
        $expenses = $this->sumByType($this->transactions, 'expense');
        $netMovement = $cashRevenues - $expenses;

        $this->summary = [
            'opening_balance' => $openingBalance,
            'revenues' => $revenues,
            'cash_revenues' => $cashRevenues,
            'visa_revenues' => $visaRevenues,
            'purchases' => $purchases,
            'cash_withdrawals' => $cashWithdrawals,
            'tips' => $tips,
            'salaries' => $salaries,
            'expenses' => $expenses,
            'net_movement' => $netMovement,
            'closing_balance' => $openingBalance + $netMovement,
        ];

        $runningBalance = $openingBalance;
        $this->dailyReports = $this->transactions
            ->groupBy('date')
            ->map(function (Collection $dayTransactions, string $date) use (&$runningBalance) {
                $revenues = $this->sumByType($dayTransactions, 'revenue');
                $cashRevenues = $this->sumRevenuesByPaymentMethod($dayTransactions, 'cash');
                $visaRevenues = $this->sumRevenuesByPaymentMethod($dayTransactions, 'visa');
                $purchases = $this->sumExpensesByKind($dayTransactions, 'purchase');
                $cashWithdrawals = $this->sumExpensesByKind($dayTransactions, 'cash_withdrawal');
                $tips = $this->sumExpensesByKind($dayTransactions, 'tip');
                $salaries = $this->sumExpensesByKind($dayTransactions, 'salary');
                $expenses = $this->sumByType($dayTransactions, 'expense');
                $net = $cashRevenues - $expenses;
                $runningBalance += $net;

                return [
                    'date' => $date,
                    'revenues' => $revenues,
                    'cash_revenues' => $cashRevenues,
                    'visa_revenues' => $visaRevenues,
                    'purchases' => $purchases,
                    'cash_withdrawals' => $cashWithdrawals,
                    'tips' => $tips,
                    'salaries' => $salaries,
                    'expenses' => $expenses,
                    'net' => $net,
                    'balance' => $runningBalance,
                ];
            })
            ->values();
    }

    private function normalizeDateRange(): void
    {
        $this->fromDate = $this->fromDate ?: Carbon::now(self::OFFICIAL_TIMEZONE)->startOfMonth()->toDateString();
        $this->toDate = $this->toDate ?: Carbon::now(self::OFFICIAL_TIMEZONE)->toDateString();

        if (Carbon::parse($this->fromDate)->greaterThan(Carbon::parse($this->toDate))) {
            [$this->fromDate, $this->toDate] = [$this->toDate, $this->fromDate];
        }
    }

    private function calculateNet(Collection $transactions): float
    {
        return $this->sumRevenuesByPaymentMethod($transactions, 'cash') - $this->sumByType($transactions, 'expense');
    }

    private function sumByType(Collection $transactions, string $type): float
    {
        return (float) $transactions
            ->where('type', $type)
            ->sum('amount');
    }

    private function sumExpensesByKind(Collection $transactions, string $kind): float
    {
        return (float) $transactions
            ->where('type', 'expense')
            ->filter(fn (AccountTransaction $transaction) => ($transaction->expense_kind ?: 'purchase') === $kind)
            ->sum('amount');
    }

    private function sumRevenuesByPaymentMethod(Collection $transactions, string $paymentMethod): float
    {
        return (float) $transactions
            ->where('type', 'revenue')
            ->where('payment_method', $paymentMethod)
            ->sum('amount');
    }
}
