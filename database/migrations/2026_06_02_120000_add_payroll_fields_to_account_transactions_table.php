<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('revenue_kind')->constrained()->nullOnDelete();
            $table->string('payroll_month', 7)->nullable()->after('employee_id');

            $table->index(['account', 'payroll_month', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropIndex(['account', 'payroll_month', 'employee_id']);
            $table->dropConstrainedForeignId('employee_id');
            $table->dropColumn('payroll_month');
        });
    }
};
