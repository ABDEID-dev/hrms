<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('account_transactions', 'invoice_image_path')) {
                $table->string('invoice_image_path')->nullable()->after('has_invoice');
            }
        });
    }

    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('account_transactions', 'invoice_image_path')) {
                $table->dropColumn('invoice_image_path');
            }
        });
    }
};
