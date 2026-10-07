<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_requests', function (Blueprint $table) {
            $table->longText('admin_response')->nullable()->after('body');
            $table->string('reviewed_by')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('employee_requests', function (Blueprint $table) {
            $table->dropColumn(['admin_response', 'reviewed_by', 'reviewed_at']);
        });
    }
};
