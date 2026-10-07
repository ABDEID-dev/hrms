<?php

namespace App\Livewire\Accounts;

use App\Models\AccountTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class TreasuryAudit extends Component
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    private const AUDIT_FIELDS = [
        'date' => 'accounts.date',
        'type' => 'ui.type',
        'expense_kind' => 'accounts.expense_kind',
        'revenue_kind' => 'accounts.revenues',
        'payment_method' => 'accounts.cash_visa',
        'amount' => 'accounts.amount_for_withdrawal',
        'employee_name' => 'accounts.employee_name',
        'service' => 'accounts.service',
        'withdrawn_to' => 'accounts.withdrawn_to',
        'customer_name' => 'accounts.customer_name',
        'quantity' => 'accounts.qty',
        'unit_price' => 'accounts.unit_price',
        'has_invoice' => 'accounts.has_invoice',
        'note' => 'accounts.note',
        'account' => 'ui.account',
    ];

    public string $account = 'all';

    public string $fromDate;

    public string $toDate;

    public array $dailyRows = [];

    public array $historicalRows = [];

    public array $auditRows = [];

    public array $totals = [
        'revenues' => 0,
        'cash_revenues' => 0,
        'visa_revenues' => 0,
        'expenses' => 0,
        'purchases' => 0,
        'cash_withdrawals' => 0,
        'tips' => 0,
        'advances' => 0,
        'closing_cash' => 0,
        'audit_count' => 0,
    ];

    public array $accountLabels = [
        'all' => 'كل الخزن',
        'maktoom' => 'مكتوم',
        'avani' => 'افاني',
        'perfumes' => 'العطور',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('Admin'), 403);

        $today = Carbon::now(self::OFFICIAL_TIMEZONE);
        $this->fromDate = $today->copy()->startOfMonth()->toDateString();
        $this->toDate = $today->toDateString();

        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.accounts.treasury-audit');
    }

    public function applyFilters(): void
    {
        $this->validate([
            'account' => ['required', 'in:all,maktoom,avani,perfumes'],
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date', 'after_or_equal:fromDate'],
        ]);

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
        $from = Carbon::parse($this->fromDate, self::OFFICIAL_TIMEZONE)->toDateString();
        $to = Carbon::parse($this->toDate, self::OFFICIAL_TIMEZONE)->toDateString();
        $rawAuditRows = $this->rawAuditRows();

        $this->dailyRows = $this->buildDailyRows($from, $to);
        $this->historicalRows = $this->buildHistoricalRows($rawAuditRows, $from, $to);
        $this->auditRows = $this->readAuditRows($from, $to, $rawAuditRows);

        $rows = collect($this->dailyRows);
        $this->totals = [
            'revenues' => (float) $rows->sum('revenues'),
            'cash_revenues' => (float) $rows->sum('cash_revenues'),
            'visa_revenues' => (float) $rows->sum('visa_revenues'),
            'expenses' => (float) $rows->sum('expenses'),
            'purchases' => (float) $rows->sum('purchases'),
            'cash_withdrawals' => (float) $rows->sum('cash_withdrawals'),
            'tips' => (float) $rows->sum('tips'),
            'advances' => (float) $rows->sum('advances'),
            'closing_cash' => (float) ($rows->last()['closing_cash'] ?? 0),
            'audit_count' => count($this->auditRows),
        ];
    }

    private function buildDailyRows(string $from, string $to): array
    {
        $openingTransactions = $this->transactionsQuery()
            ->whereDate('date', '<', $from)
            ->get();

        $transactions = $this->transactionsQuery()
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $runningCash = $this->calculateCashBalance($openingTransactions);
        $days = [];
        $cursor = Carbon::parse($from, self::OFFICIAL_TIMEZONE);
        $end = Carbon::parse($to, self::OFFICIAL_TIMEZONE);
        $transactionsByDate = $transactions->groupBy('date');

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $dayTransactions = $transactionsByDate->get($date, collect());
            $cashRevenues = $this->sumRevenuesByPaymentMethod($dayTransactions, 'cash');
            $visaRevenues = $this->sumRevenuesByPaymentMethod($dayTransactions, 'visa');
            $revenues = $this->sumByType($dayTransactions, 'revenue');
            $purchases = $this->sumExpensesByKind($dayTransactions, 'purchase');
            $cashWithdrawals = $this->sumExpensesByKind($dayTransactions, 'cash_withdrawal');
            $tips = $this->sumExpensesByKind($dayTransactions, 'tip');
            $advances = $this->sumExpensesByKind($dayTransactions, 'advance');
            $expenses = $purchases + $cashWithdrawals + $tips + $advances;
            $netCash = $cashRevenues - $expenses;
            $openingCash = $runningCash;
            $runningCash += $netCash;

            $days[] = [
                'date' => $date,
                'opening_cash' => $openingCash,
                'revenues' => $revenues,
                'cash_revenues' => $cashRevenues,
                'visa_revenues' => $visaRevenues,
                'expenses' => $expenses,
                'purchases' => $purchases,
                'cash_withdrawals' => $cashWithdrawals,
                'tips' => $tips,
                'advances' => $advances,
                'net_cash' => $netCash,
                'closing_cash' => $runningCash,
                'transactions_count' => $dayTransactions->count(),
            ];

            $cursor->addDay();
        }

        return array_reverse($days);
    }

    private function rawAuditRows(): Collection
    {
        $rows = [];

        foreach ($this->activityLogFiles() as $file) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (! str_contains($line, 'AccountTransaction')) {
                    continue;
                }

                $row = $this->parseAuditLine($line);
                if (! $row) {
                    continue;
                }

                $rows[] = $row;
            }
        }

        return collect($rows)
            ->sortBy('logged_at')
            ->values();
    }

    private function readAuditRows(string $from, string $to, Collection $rows): array
    {
        return $this->replayAuditRows($rows, $from, $to)
            ->sortByDesc('logged_at')
            ->take(500)
            ->values()
            ->all();
    }

    private function buildHistoricalRows(Collection $rows, string $from, string $to): array
    {
        $currentRows = collect($this->dailyRows)->keyBy('date');
        $historicalRows = [];
        $cursor = Carbon::parse($from, self::OFFICIAL_TIMEZONE);
        $end = Carbon::parse($to, self::OFFICIAL_TIMEZONE);

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $cutoff = $cursor->copy()->addDay()->setTime(10, 0);
            $states = $this->statesAt($rows, $cutoff);
            $historical = $this->summarizeHistoricalDate($states, $date);
            $current = $currentRows->get($date, []);

            $historicalRows[] = [
                'date' => $date,
                'cutoff_at' => $cutoff->format('Y-m-d H:i'),
                'revenues' => $historical['revenues'],
                'expenses' => $historical['expenses'],
                'cash_movement' => $historical['cash_movement'],
                'closing_cash' => $historical['closing_cash'],
                'current_closing_cash' => (float) ($current['closing_cash'] ?? 0),
                'cash_difference' => (float) ($current['closing_cash'] ?? 0) - $historical['closing_cash'],
                'late_changes' => $this->lateChangesCount($rows, $date, $cutoff),
            ];

            $cursor->addDay();
        }

        return array_reverse($historicalRows);
    }

    private function statesAt(Collection $rows, Carbon $cutoff): array
    {
        $states = [];

        foreach ($rows as $row) {
            if (Carbon::parse($row['logged_at'], self::OFFICIAL_TIMEZONE)->greaterThan($cutoff)) {
                break;
            }

            $modelKey = (string) ($row['model_id'] ?? '');
            $knownState = $states[$modelKey] ?? [];
            $databaseState = [];

            if (! $knownState && $modelKey !== '') {
                $databaseState = AccountTransaction::withTrashed()->find($row['model_id'])?->getAttributes() ?? [];
            }

            [, $afterState] = $this->auditBeforeAfterStates($row, $knownState, $databaseState);

            if ($modelKey === '') {
                continue;
            }

            if ($row['action'] === 'deleted') {
                unset($states[$modelKey]);
            } elseif ($afterState) {
                $states[$modelKey] = $afterState;
            }
        }

        return $states;
    }

    private function summarizeHistoricalDate(array $states, string $date): array
    {
        $revenues = 0.0;
        $expenses = 0.0;
        $cashMovement = 0.0;
        $closingCash = 0.0;

        foreach ($states as $state) {
            if (! $this->stateMatchesSelectedAccount($state)) {
                continue;
            }

            $stateDate = $state['date'] ?? null;

            if ($stateDate && $stateDate <= $date) {
                $closingCash += $this->cashEffect($state);
            }

            if ($stateDate !== $date) {
                continue;
            }

            if (($state['type'] ?? null) === 'revenue') {
                $revenues += (float) ($state['amount'] ?? 0);
            } elseif (($state['type'] ?? null) === 'expense') {
                $expenses += (float) ($state['amount'] ?? 0);
            }

            $cashMovement += $this->cashEffect($state);
        }

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'cash_movement' => $cashMovement,
            'closing_cash' => $closingCash,
        ];
    }

    private function lateChangesCount(Collection $rows, string $date, Carbon $cutoff): int
    {
        $states = [];
        $count = 0;

        foreach ($rows as $row) {
            $modelKey = (string) ($row['model_id'] ?? '');
            $knownState = $states[$modelKey] ?? [];
            $databaseState = [];

            if (! $knownState && $modelKey !== '') {
                $databaseState = AccountTransaction::withTrashed()->find($row['model_id'])?->getAttributes() ?? [];
            }

            [$beforeState, $afterState] = $this->auditBeforeAfterStates($row, $knownState, $databaseState);
            $loggedAt = Carbon::parse($row['logged_at'], self::OFFICIAL_TIMEZONE);

            if ($loggedAt->greaterThan($cutoff)) {
                foreach (array_filter([$beforeState, $afterState]) as $state) {
                    if (($state['date'] ?? null) === $date && $this->stateMatchesSelectedAccount($state)) {
                        $count++;
                        break;
                    }
                }
            }

            if ($modelKey === '') {
                continue;
            }

            if ($row['action'] === 'deleted') {
                unset($states[$modelKey]);
            } elseif ($afterState) {
                $states[$modelKey] = $afterState;
            }
        }

        return $count;
    }

    private function replayAuditRows(Collection $rows, string $from, string $to): Collection
    {
        $states = [];
        $cashBalances = [
            'maktoom' => 0.0,
            'avani' => 0.0,
            'perfumes' => 0.0,
        ];
        $visibleRows = collect();

        foreach ($rows as $row) {
            $modelKey = (string) ($row['model_id'] ?? '');
            $knownState = $states[$modelKey] ?? [];
            $databaseState = [];

            if (! $knownState && $modelKey !== '') {
                $databaseState = AccountTransaction::withTrashed()->find($row['model_id'])?->getAttributes() ?? [];
            }

            [$beforeState, $afterState] = $this->auditBeforeAfterStates($row, $knownState, $databaseState);
            $beforeAccount = $beforeState['account'] ?? null;
            $afterAccount = $afterState['account'] ?? null;
            $row['account'] = $afterAccount ?? $beforeAccount ?? $row['account'];

            $cashBefore = $this->selectedCashBalance($cashBalances);
            $beforeEffect = $this->cashEffect($beforeState);
            $afterEffect = $this->cashEffect($afterState);

            if ($beforeAccount) {
                $cashBalances[$beforeAccount] = ($cashBalances[$beforeAccount] ?? 0) - $beforeEffect;
            }

            if ($afterAccount) {
                $cashBalances[$afterAccount] = ($cashBalances[$afterAccount] ?? 0) + $afterEffect;
            }

            $cashAfter = $this->selectedCashBalance($cashBalances);

            if ($modelKey !== '') {
                if ($row['action'] === 'deleted') {
                    unset($states[$modelKey]);
                } elseif ($afterState) {
                    $states[$modelKey] = $afterState;
                }
            }

            $touchesSelectedAccount = $this->account === 'all'
                || $beforeAccount === $this->account
                || $afterAccount === $this->account;
            $loggedDate = Carbon::parse($row['logged_at'], self::OFFICIAL_TIMEZONE)->toDateString();

            if (! $touchesSelectedAccount || $loggedDate < $from || $loggedDate > $to) {
                continue;
            }

            $displayData = $afterState ?: $beforeState;
            $row['snapshot'] = $this->auditSnapshot($displayData);
            $row['changes'] = $this->auditChanges($beforeState, $afterState);
            $row['summary'] = $this->auditSummary($displayData);
            $row['cash_before'] = $cashBefore;
            $row['cash_after'] = $cashAfter;
            $row['cash_change'] = $cashAfter - $cashBefore;

            if ($row['action'] === 'updated' && $row['changes'] === [] && abs($row['cash_change']) < 0.005) {
                continue;
            }

            $visibleRows->push($row);
        }

        return $visibleRows;
    }

    private function auditBeforeAfterStates(array $row, array $knownState, array $databaseState): array
    {
        $old = $row['old'];
        $new = $row['new'];
        $attributes = $row['attributes'];

        if ($row['action'] === 'created') {
            return [[], $attributes ?: $new];
        }

        if ($row['action'] === 'deleted') {
            return [$attributes ?: $knownState ?: array_merge($databaseState, $old), []];
        }

        $before = array_merge($databaseState, $knownState, $old);

        return [$before, array_merge($before, $new)];
    }

    private function parseAuditLine(string $line): ?array
    {
        $line = trim($line);

        if (! preg_match('/^\[(?<date>.*?)\].*?:\s*(?<message>.*?)\s+(?<json>\{.*\})\s*$/u', $line, $matches)) {
            return null;
        }

        $context = json_decode($matches['json'], true);
        if (! is_array($context) || ($context['model'] ?? null) !== AccountTransaction::class) {
            return null;
        }

        $old = (array) ($context['old'] ?? []);
        $new = (array) ($context['new'] ?? []);
        $attributes = (array) ($context['attributes'] ?? []);

        return [
            'logged_at' => Carbon::parse($matches['date'])->timezone(self::OFFICIAL_TIMEZONE)->format('Y-m-d H:i:s'),
            'action' => (string) ($context['action'] ?? ''),
            'message' => trim($matches['message']),
            'actor_name' => $context['actor_name'] ?? '---',
            'actor_username' => $context['actor_username'] ?? '---',
            'ip' => $context['ip'] ?? '---',
            'url' => $context['url'] ?? '',
            'model_id' => $context['model_id'] ?? null,
            'account' => $new['account'] ?? $old['account'] ?? $attributes['account'] ?? null,
            'old' => $old,
            'new' => $new,
            'attributes' => $attributes,
            'snapshot' => $this->auditSnapshot($new ?: $attributes ?: $old),
            'changes' => $this->auditChanges($old, $new),
            'summary' => $this->auditSummary($new ?: $attributes ?: $old),
        ];
    }

    private function auditSnapshot(array $data): array
    {
        $snapshot = [];

        foreach (self::AUDIT_FIELDS as $field => $labelKey) {
            if (! array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                continue;
            }

            $snapshot[] = [
                'field' => $field,
                'label' => __($labelKey),
                'value' => $this->formatAuditValue($field, $data[$field]),
            ];
        }

        return $snapshot;
    }

    private function auditChanges(array $old, array $new): array
    {
        $changes = [];

        foreach (self::AUDIT_FIELDS as $field => $labelKey) {
            $oldValue = $old[$field] ?? null;
            $newValue = $new[$field] ?? null;

            if ($this->normalizeAuditValue($oldValue) === $this->normalizeAuditValue($newValue)) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => __($labelKey),
                'old' => $this->formatAuditValue($field, $oldValue),
                'new' => $this->formatAuditValue($field, $newValue),
            ];
        }

        return $changes;
    }

    private function auditSummary(array $data): array
    {
        return [
            'type' => $this->formatAuditValue('type', $data['type'] ?? null),
            'date' => $this->formatAuditValue('date', $data['date'] ?? null),
            'amount' => $this->formatAuditValue('amount', $data['amount'] ?? null),
            'person' => $data['employee_name'] ?? $data['withdrawn_to'] ?? $data['customer_name'] ?? null,
            'description' => $data['service'] ?? $data['note'] ?? null,
        ];
    }

    private function formatAuditValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '---';
        }

        if (in_array($field, ['amount', 'unit_price'], true)) {
            return 'AED '.number_format((float) $value, 2);
        }

        if ($field === 'quantity') {
            return number_format((float) $value, 2);
        }

        if ($field === 'type') {
            return $value === 'revenue' ? __('accounts.revenues') : ($value === 'expense' ? __('accounts.expenses') : (string) $value);
        }

        if ($field === 'payment_method') {
            return $value === 'cash' ? __('accounts.cash') : ($value === 'visa' ? __('accounts.visa') : (string) $value);
        }

        if ($field === 'expense_kind') {
            return match ($value) {
                'purchase' => __('accounts.purchases'),
                'cash_withdrawal' => __('accounts.cash_withdrawal'),
                'tip' => __('accounts.tip'),
                'advance' => 'سلفة موظف',
                default => (string) $value,
            };
        }

        if ($field === 'has_invoice') {
            return $value ? __('accounts.invoice_yes') : __('accounts.invoice_no');
        }

        if ($field === 'account') {
            return $this->accountLabels[$value] ?? (string) $value;
        }

        return (string) $value;
    }

    private function normalizeAuditValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return (string) (float) $value;
        }

        return trim((string) $value);
    }

    private function selectedCashBalance(array $cashBalances): float
    {
        if ($this->account !== 'all') {
            return (float) ($cashBalances[$this->account] ?? 0);
        }

        return (float) array_sum($cashBalances);
    }

    private function stateMatchesSelectedAccount(array $state): bool
    {
        return $this->account === 'all' || ($state['account'] ?? null) === $this->account;
    }

    private function cashEffect(array $state): float
    {
        if (! $state) {
            return 0.0;
        }

        $amount = (float) ($state['amount'] ?? 0);

        if (($state['type'] ?? null) === 'revenue') {
            return ($state['payment_method'] ?? null) === 'cash' ? $amount : 0.0;
        }

        if (($state['type'] ?? null) === 'expense') {
            return -1 * $amount;
        }

        return 0.0;
    }

    private function activityLogFiles(): array
    {
        $files = glob(storage_path('logs/activity*.log')) ?: [];

        return collect($files)
            ->unique()
            ->filter(fn (string $file) => File::exists($file))
            ->values()
            ->all();
    }

    private function transactionsQuery()
    {
        return AccountTransaction::query()
            ->when($this->account !== 'all', fn ($query) => $query->where('account', $this->account));
    }

    private function calculateCashBalance(Collection $transactions): float
    {
        return $this->sumRevenuesByPaymentMethod($transactions, 'cash') - $this->sumByType($transactions, 'expense');
    }

    private function sumByType(Collection $transactions, string $type): float
    {
        return (float) $transactions->where('type', $type)->sum('amount');
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
