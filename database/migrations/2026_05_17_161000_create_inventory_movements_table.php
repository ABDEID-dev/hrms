<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_product_id')->constrained()->cascadeOnDelete();
            $table->string('account')->index();
            $table->enum('type', ['purchase', 'sale', 'adjustment']);
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->decimal('balance_after', 14, 3)->default(0);
            $table->timestamp('occurred_at')->index();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('note')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['inventory_product_id', 'type']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
