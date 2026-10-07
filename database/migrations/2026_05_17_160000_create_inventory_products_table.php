<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->string('account')->index();
            $table->string('category')->nullable()->index();
            $table->string('name');
            $table->string('sku')->nullable()->index();
            $table->enum('unit', ['piece', 'gram'])->default('piece');
            $table->decimal('stock_quantity', 14, 3)->default(0);
            $table->decimal('sold_quantity', 14, 3)->default(0);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('low_stock_threshold', 14, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account', 'unit']);
            $table->index(['account', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
    }
};
