<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_invoice_item_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_invoice_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['salon_invoice_item_id', 'employee_id'], 'invoice_item_employee_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_invoice_item_employee');
    }
};
