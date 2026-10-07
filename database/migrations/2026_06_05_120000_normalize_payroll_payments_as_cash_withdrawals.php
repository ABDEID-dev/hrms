<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('account_transactions')
            ->where('type', 'revenue')
            ->where('revenue_kind', 'payroll_transfer')
            ->whereNotNull('payroll_month')
            ->orderBy('id')
            ->chunkById(100, function ($transactions) {
                foreach ($transactions as $transaction) {
                    DB::table('account_transactions')
                        ->where('id', $transaction->id)
                        ->update([
                            'date' => $this->endOfPayrollMonth($transaction->payroll_month),
                            'updated_at' => now(),
                        ]);
                }
            });

        DB::table('account_transactions')
            ->where('type', 'expense')
            ->where('expense_kind', 'salary')
            ->whereNotNull('payroll_month')
            ->orderBy('id')
            ->chunkById(100, function ($transactions) {
                foreach ($transactions as $transaction) {
                    DB::table('account_transactions')
                        ->where('id', $transaction->id)
                        ->update([
                            'expense_kind' => 'cash_withdrawal',
                            'date' => $this->endOfPayrollMonth($transaction->payroll_month),
                            'service' => null,
                            'quantity' => 1,
                            'has_invoice' => null,
                            'withdrawn_to' => $transaction->withdrawn_to
                                ?: $transaction->employee_name
                                ?: $this->nameFromSalaryNote($transaction->note),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('account_transactions')
            ->where('type', 'expense')
            ->where('expense_kind', 'cash_withdrawal')
            ->whereNotNull('payroll_month')
            ->where('note', 'like', 'Salary payment for %')
            ->update([
                'expense_kind' => 'salary',
                'withdrawn_to' => null,
                'updated_at' => now(),
            ]);
    }

    private function endOfPayrollMonth(string $payrollMonth): string
    {
        return Carbon::createFromFormat('Y-m', $payrollMonth, 'Asia/Dubai')
            ->endOfMonth()
            ->toDateString();
    }

    private function nameFromSalaryNote(?string $note): ?string
    {
        if (! $note || ! preg_match('/^Salary payment for (.+) - \d{4}-\d{2}$/u', $note, $matches)) {
            return null;
        }

        return $matches[1];
    }
};
