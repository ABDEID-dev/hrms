<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salon_service_id')->constrained()->cascadeOnDelete();
            $table->string('service_name');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_invoice_items');
    }
};
