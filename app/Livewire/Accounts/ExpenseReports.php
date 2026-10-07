<?php

namespace App\Livewire\Accounts;

use App\Models\AccountTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseReports extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $account = 'all';

    public string $expenseKind = 'all';

    public string $fromDate;

    public string $toDate;

    public string $search = '';

    private array $accountNames = [
        'maktoom' => 'Maktoum',
        'avani' => 'Avani',
        'perfumes' => 'Perfumes',
    ];

    public function mount(): void
    {
        $this->setThisMonth();

        if (! array_key_exists($this->account, $this->availableAccounts())) {
            $this->account = 'all';
        }
    }

    public function render()
    {
        $baseQuery = $this->transactionsQuery()
            ->latest('date')
            ->latest('created_at')
            ->latest('id');

        $summaryTransactions = (clone $baseQuery)->get();

        return view('livewire.accounts.expense-reports', [
            'accountOptions' => $this->availableAccounts(),
            'totals' => $this->totals($summaryTransactions),
            'accountRows' => $this->accountRows($summaryTransactions),
            'kindRows' => $this->kindRows($summaryTransactions),
            'transactions' => $baseQuery->paginate(100),
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['account', 'expenseKind', 'fromDate', 'toDate', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function setThisMonth(): void
    {
        $now = Carbon::now('Asia/Dubai');
        $this->fromDate = $now->copy()->startOfMonth()->toDateString();
        $this->toDate = $now->copy()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->account = 'all';
        $this->expenseKind = 'all';
        $this->search = '';
        $this->setThisMonth();
        $this->resetPage();
    }

    public function updatedFromDate(): void
    {
        $this->normalizeDateRange();
    }

    public function updatedToDate(): void
    {
        $this->normalizeDateRange();
    }

    private function transactionsQuery(): Builder
    {
        $accounts = array_keys($this->availableAccounts());
        $selectedAccounts = $this->account === 'all'
            ? $accounts
            : array_values(array_intersect([$this->account], $accounts));

        if ($selectedAccounts === []) {
            $selectedAccounts = $accounts;
        }

        $this->normalizeDateRange();

        return AccountTransaction::query()
            ->where('type', 'expense')
            ->whereIn('account', $selectedAccounts)
            ->whereBetween('date', [$this->fromDate, $this->toDate])
            ->when($this->expenseKind !== 'all', fn (Builder $query) => $query->where('expense_kind', $this->expenseKind))
            ->when(trim($this->search) !== '', function (Builder $query) {
                $search = '%'.trim($this->search).'%';

                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('service', 'like', $search)
                        ->orWhere('withdrawn_to', 'like', $search)
                        ->orWhere('note', 'like', $search);
                });
            });
    }

    private function totals(Collection $transactions): array
    {
        return [
            'total' => (float) $transactions->sum('amount'),
            'purchase' => $this->sumKind($transactions, 'purchase'),
            'cash_withdrawal' => $this->sumKind($transactions, 'cash_withdrawal'),
            'tip' => $this->sumKind($transactions, 'tip'),
            'advance' => $this->sumKind($transactions, 'advance'),
            'count' => $transactions->count(),
        ];
    }

    private function accountRows(Collection $transactions): Collection
    {
        return collect($this->availableAccounts())
            ->map(function (string $name, string $account) use ($transactions) {
                $rows = $transactions->where('account', $account);

                return [
                    'account' => $account,
                    'name' => $name,
                    'purchase' => $this->sumKind($rows, 'purchase'),
                    'cash_withdrawal' => $this->sumKind($rows, 'cash_withdrawal'),
                    'tip' => $this->sumKind($rows, 'tip'),
                    'advance' => $this->sumKind($rows, 'advance'),
                    'total' => (float) $rows->sum('amount'),
                    'count' => $rows->count(),
                ];
            })
            ->filter(fn (array $row) => $row['count'] > 0 || $this->account !== 'all')
            ->values();
    }

    private function kindRows(Collection $transactions): Collection
    {
        return collect(['purchase', 'cash_withdrawal', 'tip', 'advance'])
            ->map(fn (string $kind) => [
                'kind' => $kind,
                'label' => $this->expenseKindLabel($kind),
                'total' => $this->sumKind($transactions, $kind),
                'count' => $transactions->filter(fn (AccountTransaction $transaction) => ($transaction->expense_kind ?: 'purchase') === $kind)->count(),
            ]);
    }

    private function sumKind(Collection $transactions, string $kind): float
    {
        return (float) $transactions
            ->filter(fn (AccountTransaction $transaction) => ($transaction->expense_kind ?: 'purchase') === $kind)
            ->sum('amount');
    }

    public function accountLabel(?string $account): string
    {
        return $this->accountNames[$account] ?? (string) $account;
    }

    public function expenseKindLabel(?string $kind): string
    {
        if (($kind ?: 'purchase') === 'advance') {
            return 'سلف الموظفين';
        }

        return match ($kind ?: 'purchase') {
            'cash_withdrawal' => 'السحوبات',
            'tip' => 'التيبس',
            default => 'المشتريات',
        };
    }

    public function expenseTitle(AccountTransaction $transaction): string
    {
        return ($transaction->expense_kind ?: 'purchase') === 'purchase'
            ? ($transaction->service ?: '---')
            : ($transaction->withdrawn_to ?: '---');
    }

    private function availableAccounts(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return collect($this->accountNames)
            ->filter(fn (string $name, string $account) => $user->canAccessAccountBranch($account))
            ->all();
    }

    private function normalizeDateRange(): void
    {
        try {
            $from = Carbon::parse($this->fromDate ?: now('Asia/Dubai'), 'Asia/Dubai')->toDateString();
        } catch (\Throwable) {
            $from = Carbon::now('Asia/Dubai')->startOfMonth()->toDateString();
        }

        try {
            $to = Carbon::parse($this->toDate ?: now('Asia/Dubai'), 'Asia/Dubai')->toDateString();
        } catch (\Throwable) {
            $to = Carbon::now('Asia/Dubai')->endOfMonth()->toDateString();
        }

        if (Carbon::parse($from)->gt(Carbon::parse($to))) {
            [$from, $to] = [$to, $from];
        }

        $this->fromDate = $from;
        $this->toDate = $to;
    }
}
