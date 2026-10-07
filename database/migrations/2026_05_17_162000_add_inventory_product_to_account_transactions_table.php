<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->string('revenue_kind')->default('service')->after('type');
            $table->foreignId('inventory_product_id')
                ->nullable()
                ->after('revenue_kind')
                ->constrained('inventory_products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inventory_product_id');
            $table->dropColumn('revenue_kind');
        });
    }
};
