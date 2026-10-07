<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salon_invoices', function (Blueprint $table) {
            $table->string('invoice_language', 5)->default('ar')->after('branch');
        });
    }

    public function down(): void
    {
        Schema::table('salon_invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_language');
        });
    }
};
