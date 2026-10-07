<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_requests', 'employee_hidden_at')) {
                $table->timestamp('employee_hidden_at')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_requests', function (Blueprint $table) {
            if (Schema::hasColumn('employee_requests', 'employee_hidden_at')) {
                $table->dropColumn('employee_hidden_at');
            }
        });
    }
};
