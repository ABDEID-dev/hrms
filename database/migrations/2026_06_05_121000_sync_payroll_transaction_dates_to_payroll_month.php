<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('account_transactions')
            ->whereNotNull('payroll_month')
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
    }

    public function down(): void
    {
        //
    }

    private function endOfPayrollMonth(string $payrollMonth): string
    {
        return Carbon::createFromFormat('Y-m', $payrollMonth, 'Asia/Dubai')
            ->endOfMonth()
            ->toDateString();
    }
};
