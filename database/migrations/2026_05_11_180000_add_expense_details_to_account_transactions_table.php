<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->string('expense_kind')->nullable()->after('type');
            $table->boolean('has_invoice')->nullable()->after('payment_method');
            $table->string('withdrawn_to')->nullable()->after('has_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropColumn(['expense_kind', 'has_invoice', 'withdrawn_to']);
        });
    }
};
